<?php

namespace Modules\Orders\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Orders\Models\Payment;
use Modules\Orders\Services\PaymentService;

/**
 * کنترلر پرداخت مشترک — درگاه + callback عمومی
 *
 * مسیرها:
 *   GET  /payment/{payment}/gateway   → صفحه درگاه (شبیه‌ساز)
 *   POST /payment/{payment}/complete  → تأیید دستی موفق (شبیه‌ساز)
 *   POST /payment/{payment}/fail      → ثبت عدم موفقیت
 *   GET  /payment/{provider}/callback → callback عمومی درگاه (status=ok|fail)
 */
class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {}

    /**
     * صفحه درگاه پرداخت (شبیه‌ساز تا پیاده‌سازی فاز ۱۳)
     */
    public function gateway(Payment $payment)
    {
        $this->authorizePayment($payment);

        // زرین‌پال (واقعی/سندباکس) → هدایت مستقیم به صفحه StartPay درگاه
        if ($this->paymentService->activeDriver() === 'zarinpal') {
            try {
                return redirect()->away($this->paymentService->zarinpalRequest($payment));
            } catch (\Throwable $e) {
                return view('orders::payment.gateway', [
                    'payment' => $payment,
                    'gatewayName' => $this->paymentService->driverLabel(),
                    'gatewayError' => $e->getMessage(),
                ]);
            }
        }

        return view('orders::payment.gateway', [
            'payment' => $payment,
            'gatewayName' => $this->paymentService->driverLabel(),
        ]);
    }

    /**
     * تأیید موفق — کاربر دکمه «پرداخت موفق» را می‌زند (شبیه‌ساز)
     */
    public function complete(Request $request, Payment $payment)
    {
        $this->authorizePayment($payment);

        $validated = $request->validate([
            'transaction_id' => ['nullable', 'string', 'max:100'],
        ]);

        $this->paymentService->complete($payment, $validated['transaction_id'] ?? null);

        return $this->redirectBack($payment, 'پرداخت با موفقیت انجام شد.');
    }

    /**
     * ثبت عدم موفقیت پرداخت
     */
    public function fail(Payment $payment)
    {
        $this->authorizePayment($payment);

        $this->paymentService->fail($payment);

        return $this->redirectBack($payment, 'پرداخت ناموفق بود — دوباره تلاش کنید.', true);
    }

    /**
     * callback عمومی درگاه: /payment/{provider}/callback
     *
     * درگاه‌های واقعی (زرین‌پال/آی‌دی‌پی) کاربر را با status به این مسیر برمی‌گردانند.
     */
    public function callback(string $provider, Request $request)
    {
        $result = $this->paymentService->handleCallback($provider, $request);

        $payment = $result['payment'];

        return $this->redirectBack(
            $payment,
            $result['message'] ?? ($result['success'] ? 'پرداخت با موفقیت انجام شد.' : 'پرداخت ناموفق بود — دوباره تلاش کنید.'),
            ! $result['success']
        );
    }

    /**
     * دسترسی: فقط صاحب پرداخت یا ادمین
     */
    private function authorizePayment(Payment $payment): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user || ($payment->user_id !== $user->id && ! $user->hasPermission('manage_orders'))) {
            abort(404, 'چنین صفحه‌ای در سیستم وجود ندارد.');
        }

        if ($payment->status !== 'pending') {
            abort(404, 'چنین صفحه‌ای در سیستم وجود ندارد.');
        }
    }

    /**
     * بازگشت به سفارش مرتبط با پیام مناسب
     */
    private function redirectBack(Payment $payment, string $message, bool $isError = false)
    {
        $order = $payment->order;

        $route = $order ? route('user.orders.show', $order->order_number) : route('user.dashboard');

        return redirect($route)->with($isError ? 'error' : 'success', $message);
    }
}
