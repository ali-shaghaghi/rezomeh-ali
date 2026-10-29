<?php

namespace Modules\Orders\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Orders\Enums\PaymentMethod;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\Payment;
use Modules\Settings\Models\Setting;

/**
 * سرویس پرداخت مشترک — یک API واحد برای هر سه ماژول:
 *   Products (چندتراکنشی) · Consultation · ProjectDev (اقساطی)
 *
 * جریان:
 *   create()      → ثبت پرداخت در انتظار
 *   start()       → تعیین مسیر: manual (منتظر ادمین) یا gateway
 *   complete()    → تأیید موفق (callback درگاه) + تکمیل سفارش
 *   fail()        → ثبت عدم موفقیت
 *
 * callback عمومی: /payment/{provider}/callback?payment={id}&status={ok|fail}
 *
 * ponytail: درگاه واقعی هنوز پیاده نشده؛ فعلاً روی «شبیه‌ساز» است.
 *           upgrade path: ساخت درایورهای واقعی در Modules/Orders/Gateways/
 */
class PaymentService
{
    public const PROVIDERS = ['zarinpal', 'idpay', 'digify', 'digipay', 'simulator'];

    public function __construct(
        private readonly OrderService $orderService,
    ) {}

    // ===== تنظیمات درگاه از Settings =====

    /**
     * درگاه فعال از Settings (پنل ادمین)
     */
    public function activeDriver(): string
    {
        $driver = (string) Setting::get('payment_driver', 'manual');

        return in_array($driver, self::PROVIDERS, true) ? $driver : 'manual';
    }

    /**
     * نام نمایشی درگاه فعال
     */
    public function driverLabel(): string
    {
        return match ($this->activeDriver()) {
            'zarinpal' => 'زرین‌پال',
            'idpay' => 'آی‌دی‌پی',
            'digify' => 'دیجیفای',
            'digipay' => 'دیجی‌پی',
            'simulator' => 'شبیه‌ساز پرداخت',
            'manual' => 'کارت به کارت',
            default => 'درگاه پرداخت',
        };
    }

    /**
     * آیا پرداخت آنلاین فعال است؟ (درگاه غیر از manual)
     */
    public function isOnlineEnabled(): bool
    {
        return $this->activeDriver() !== 'manual';
    }

    /**
     * پرداخت اقساطی از Settings فعال است؟
     */
    public function isInstallmentEnabled(): bool
    {
        return Setting::getBool('installment_enabled', true);
    }

    /**
     * حداکثر تعداد اقساط مجاز
     */
    public function installmentMaxCount(): int
    {
        return (int) Setting::get('installment_max_count', 3);
    }

    /**
     * نرخ سود اقساطی (٪)
     */
    public function installmentInterestRate(): float
    {
        return (float) Setting::get('installment_interest_rate', 0);
    }

    /**
     * تولید شناسه تراکنش یکتا
     */
    public function generateTransactionId(): string
    {
        return 'TX-'.strtoupper(Str::random(12));
    }

    // ===== ساخت و شروع پرداخت =====

    /**
     * ثبت یک پرداخت جدید در انتظار
     *
     * @param  array{amount: float|string, method?: string|PaymentMethod, description?: string, transaction_id?: string}  $data
     */
    public function create(Order $order, array $data): Payment
    {
        $method = $data['method'] instanceof PaymentMethod
            ? $data['method']
            : PaymentMethod::fromValue($data['method'] ?? null);

        return $this->orderService->addPayment($order, [
            'user_id' => $order->user_id,
            'amount' => $data['amount'],
            'status' => 'pending',
            'payment_method' => $method->value,
            'transaction_id' => $data['transaction_id'] ?? null,
            'description' => $data['description'] ?? null,
        ]);
    }

    /**
     * شروع پرداخت — بر اساس روش، مسیر مناسب را برمی‌گرداند
     *
     * @return array{payment: Payment, redirect: ?string}
     */
    public function start(Order $order, array $data): array
    {
        $payment = $this->create($order, $data);

        $method = PaymentMethod::fromValue($payment->payment_method);

        // روش دستی → منتظر تأیید ادمین؛ callback بازگشتی ندارد
        if ($method->requiresAdminApproval()) {
            return ['payment' => $payment, 'redirect' => null];
        }

        // ponytail: در زمان شبیه‌ساز، کاربر به صفحه درگاه هدایت می‌شود.
        // هنگام اتصال درگاه واقعی، اینجا redirect واقعی درگاه تولید می‌شود.
        return [
            'payment' => $payment,
            'redirect' => route('payment.gateway', ['payment' => $payment->id]),
        ];
    }

    // ===== تکمیل / شکست =====

    /**
     * تأیید موفق پرداخت (callback درگاه)
     */
    public function complete(Payment $payment, ?string $transactionId = null): Payment
    {
        return DB::transaction(function () use ($payment, $transactionId) {
            $payment->update([
                'status' => 'completed',
                'transaction_id' => $transactionId ?? $payment->transaction_id ?? $this->generateTransactionId(),
                'paid_at' => now(),
            ]);

            $order = $payment->order;

            // به‌روزرسانی مبلغ پرداخت‌شده
            $order->paid_amount = $order->payments()->completed()->sum('amount');
            $order->save();

            $this->onPaymentCompleted($payment, $order);

            return $payment->refresh();
        });
    }

    /**
     * ثبت عدم موفقیت پرداخت
     */
    public function fail(Payment $payment): Payment
    {
        $payment->update(['status' => 'failed']);

        return $payment->refresh();
    }

    /**
     * تأیید پرداخت دستی توسط ادمین
     */
    public function approveManual(Payment $payment): Payment
    {
        return $this->complete($payment, 'MANUAL-'.strtoupper(Str::random(8)));
    }

    // ===== callback عمومی درگاه =====

    /**
     * پردازش callback درگاه: /payment/{provider}/callback
     *
     * @return array{payment: Payment, success: bool}
     */
    public function handleCallback(string $provider, Request $request): array
    {
        $payment = Payment::findOrFail($request->query('payment'));

        // زرین‌پال: تایید سروری لازم است — با کوئری‌استرینگ خودی قابل تکمیل نیست
        if ($provider === 'zarinpal') {
            return $this->zarinpalCallback($payment, $request);
        }

        $status = (string) $request->query('status', 'ok');

        if (in_array(strtolower($status), ['fail', 'failed', 'nok'], true)) {
            return ['payment' => $this->fail($payment), 'success' => false];
        }

        $transactionId = $request->query('transaction_id')
            ?? $request->query('Authority')
            ?? $request->query('track_id');

        return ['payment' => $this->complete($payment, $transactionId), 'success' => true];
    }

    // ===== زرین‌پال — درگاه واقعی (v4) با پشتیبانی سندباکس =====

    /** آیا حالت تست (سندباکس) فعال است؟ */
    public function isSandboxMode(): bool
    {
        return Setting::getBool('payment_test_mode', false);
    }

    /** هاست زرین‌پال بر اساس حالت سندباکس/واقعی */
    public function zarinpalBaseUrl(): string
    {
        return $this->isSandboxMode() ? 'https://sandbox.zarinpal.com' : 'https://payment.zarinpal.com';
    }

    /** شناسه پذیرنده زرین‌پال از تنظیمات */
    public function zarinpalMerchant(): string
    {
        return (string) (Setting::get('payment_merchant_id_zarinpal')
            ?: Setting::get('payment_merchant_id')
            ?: Setting::get('zarinpal_merchant_id')
            ?: '');
    }

    /**
     * ساخت پرداخت در زرین‌پال (request.json) و برگرداندن آدرس StartPay
     */
    public function zarinpalRequest(Payment $payment): string
    {
        $merchant = $this->zarinpalMerchant();

        if ($merchant === '') {
            throw new \RuntimeException('شناسه پذیرنده زرین‌پال ثبت نشده است — تنظیمات ← درگاه پرداخت ← زرین‌پال.');
        }

        if ($payment->status !== 'pending') {
            throw new \RuntimeException('این پرداخت قبلاً تکمیل شده است.');
        }

        $base = $this->zarinpalBaseUrl();
        $callback = route('payment.callback', ['provider' => 'zarinpal', 'payment' => $payment->id]);

        try {
            $res = Http::timeout(25)->post($base.'/pg/v4/payment/request.json', [
                'merchant_id' => $merchant,
                // زرین‌پال مبلغ را به ریال می‌خواهد (مبلغ ما تومان است)
                'amount' => (int) round(((float) $payment->amount) * 10),
                'callback_url' => $callback,
                'description' => $payment->description ?: ('پرداخت سفارش '.($payment->order->order_number ?? '')),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Zarinpal request failed: '.$e->getMessage());
            throw new \RuntimeException('اتصال به زرین‌پال برقرار نشد — اینترنت/پروکسی را بررسی کنید.');
        }

        $code = (int) ($res->json('data.code') ?? 0);
        $authority = (string) ($res->json('data.authority') ?? '');

        if ($authority === '' || ! in_array($code, [100, 101], true)) {
            throw new \RuntimeException('زرین‌پال: '.$this->zarinpalError($res));
        }

        $payment->update(['transaction_id' => $authority]);

        return $base.'/pg/StartPay/'.$authority;
    }

    /**
     * callback زرین‌پال — تایید سروری پرداخت (verify.json) و سپس تکمیل/شکست
     *
     * @return array{payment: Payment, success: bool, message?: string}
     */
    private function zarinpalCallback(Payment $payment, Request $request): array
    {
        $status = (string) $request->query('Status', (string) $request->query('status', ''));
        $authority = (string) ($request->query('Authority', $request->query('authority', '')) ?: $payment->transaction_id);

        // لغو/خطا در خود درگاه
        if ($status !== '' && strtoupper($status) !== 'OK') {
            $this->fail($payment);

            return ['payment' => $payment->refresh(), 'success' => false, 'message' => 'پرداخت در زرین‌پال لغو شد.'];
        }

        if ($payment->status !== 'pending') {
            return [
                'payment' => $payment,
                'success' => $payment->status === 'completed',
                'message' => $payment->status === 'completed' ? 'این پرداخت قبلاً با موفقیت انجام شده است.' : 'پرداخت ناموفق بود.',
            ];
        }

        $merchant = $this->zarinpalMerchant();
        $amountRials = (int) round(((float) $payment->amount) * 10);

        try {
            $res = Http::timeout(25)->post($this->zarinpalBaseUrl().'/pg/v4/payment/verify.json', [
                'merchant_id' => $merchant,
                'amount' => $amountRials,
                'authority' => $authority,
            ]);
        } catch (\Throwable $e) {
            // خطای شبکه — پرداخت pending می‌ماند تا بررسی دستی/تلاش مجدد
            Log::warning('Zarinpal verify failed: '.$e->getMessage());

            return ['payment' => $payment, 'success' => false, 'message' => 'تایید پرداخت زرین‌پال ممکن نشد (خطای شبکه) — پرداخت در انتظار بررسی ماند.'];
        }

        $code = (int) ($res->json('data.code') ?? 0);

        if ($code === 100 || $code === 101) {
            $refId = $res->json('data.ref_id');

            return [
                'payment' => $this->complete($payment, 'ZP-'.$refId),
                'success' => true,
                'message' => 'پرداخت با موفقیت انجام شد. کد پیگیری: '.$refId,
            ];
        }

        $this->fail($payment);

        return ['payment' => $payment->refresh(), 'success' => false, 'message' => 'تایید زرین‌پال ناموفق بود — '.$this->zarinpalError($res)];
    }

    /** پیام خطای زرین‌پال از پاسخ request/verify */
    private function zarinpalError($res): string
    {
        $errors = collect($res->json('errors', []) ?? [])
            ->map(fn ($e) => is_array($e) ? ($e['description'] ?? $e['message'] ?? null) : $e)
            ->filter()
            ->implode(' | ');

        return $errors !== '' ? $errors : (string) ($res->json('data.message') ?? 'کد '.$res->status());
    }

    // ===== قل‌ها (hooks) =====

    /**
     * صدور لایسنس آیتم‌های سفارش در صورتی که هنگام ساخت سفارش صادر نشده‌اند
     * (در مسیر درگاه واقعی، سفارش pending ساخته می‌شود و لایسنس بعد از تایید صادر می‌شود)
     */
    private function issueMissingLicenses(Order $order): void
    {
        if (! class_exists(\Modules\Products\Services\LicenseService::class)
            || ! class_exists(\Modules\Products\Models\License::class)) {
            return;
        }

        foreach ($order->items as $item) {
            $alreadyIssued = \Modules\Products\Models\License::where('order_item_id', $item->id)->count();
            $missing = max(0, (int) $item->quantity - $alreadyIssued);

            for ($i = 0; $i < $missing; $i++) {
                try {
                    app(\Modules\Products\Services\LicenseService::class)->issueForOrderItem($item);
                } catch (\Throwable $e) {
                    Log::warning('License issuance failed for order '.$order->id.': '.$e->getMessage());
                }
            }
        }
    }

    /**
     * پس از تکمیل موفق پرداخت — سفارش را می‌بندد.
     * سفارش اقساطی فقط زمانی کامل می‌شود که همه قسط‌ها پرداخت شوند.
     */
    protected function onPaymentCompleted(Payment $payment, Order $order): void
    {
        // صدور لایسنس‌هایی که هنگام ساخت سفارش صادر نشدند (مسیر درگاه واقعی زرین‌پال)
        $this->issueMissingLicenses($order);

        if ($order->installments()->exists()) {
            $unpaid = $order->installments()->where('status', 'unpaid')->count();

            if ($unpaid === 0) {
                $this->orderService->setStatus($order, 'completed');
            }

            return;
        }

        if ($order->status !== 'completed') {
            $this->orderService->setStatus($order, 'completed');
        }
    }

    /**
     * لیست پرداخت‌های یک کاربر (برای پنل کاربر)
     */
    public function userPayments(User $user, int $perPage = 10)
    {
        return Payment::where('user_id', $user->id)
            ->with('order')
            ->latest()
            ->paginate($perPage);
    }
}
