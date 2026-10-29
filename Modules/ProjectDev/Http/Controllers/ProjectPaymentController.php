<?php

namespace Modules\ProjectDev\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\Payment;
use Modules\Orders\Services\OrderService;
use Modules\Orders\Services\PaymentService;
use Modules\ProjectDev\Models\OrderInstallment;
use Modules\Settings\Models\Setting;

/**
 * پرداخت قسط‌های 30/30/40 — به درگاه فعال تنظیم‌شده در پنل ادمین (Settings)
 *
 * پرداخت از طریق PaymentService مشترک (Orders) ثبت و تأیید می‌شود.
 * این کنترلر فقط لایه اختصاصی اقساط را روی آن می‌سازد.
 */
class ProjectPaymentController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly PaymentService $paymentService,
    ) {}

    /**
     * شروع پرداخت قسط — ثبت پرداخت در انتظار + هدایت به درگاه
     */
    public function pay(OrderInstallment $installment)
    {
        /** @var User $user */
        $user = Auth::user();

        $order = $installment->order;

        // فقط قسط سفارش خودِ کاربر و فقط قسط پرداخت‌نشده
        if ($order->user_id !== $user->id || $installment->status !== 'unpaid') {
            abort(404, 'چنین صفحه‌ای در سیستم وجود ندارد.');
        }

        // قسط‌ها به‌ترتیب پرداخت می‌شوند (پیش‌پرداخت → نیمه کار → تحویل)
        $previous = OrderInstallment::where('order_id', $order->id)
            ->where('installment_number', '<', $installment->installment_number)
            ->where('status', 'unpaid')
            ->exists();

        if ($previous) {
            return redirect()
                ->route('user.orders.show', $order->order_number)
                ->with('error', 'ابتدا باید قسط‌های قبلی پرداخت شوند.');
        }

        // ثبت پرداخت در انتظار (تایید نهایی در callback درگاه)
        $this->orderService->addPayment($order, [
            'user_id' => $user->id,
            'amount' => $installment->amount,
            'status' => 'pending',
            'payment_method' => Setting::get('payment_driver', 'manual'),
            'description' => 'پرداخت '.$installment->title.' — سفارش '.$order->order_number,
        ]);

        $payment = $order->payments()->latest('id')->first();

        // درگاه دستی (کارت به کارت) — بدون هدایت؛ پس از تایید ادمین قسط بسته می‌شود
        if ((Setting::get('payment_driver', 'manual')) === 'manual') {
            return redirect()
                ->route('user.orders.show', $order->order_number)
                ->with('success', 'درگاه کارت به کارت فعال است — پس از واریز، پرداخت توسط ادمین تایید و قسط بسته می‌شود.');
        }

        return redirect()->route('user.projectdev.payment.gateway', $payment->id);
    }

    /**
     * صفحه شبیه‌ساز درگاه پرداخت (تا پیاده‌سازی فاز ۱۳)
     */
    public function gateway(Payment $payment)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($payment->user_id !== $user->id || $payment->status !== 'pending') {
            abort(404, 'چنین صفحه‌ای در سیستم وجود ندارد.');
        }

        return view('projectdev::user.payment-gateway', [
            'payment' => $payment,
            'gatewayName' => (Setting::get('payment_driver') ?? 'زرین‌پال') === 'idpay' ? 'آی‌دی‌پی' : 'زرین‌پال',
        ]);
    }

    /**
     * تایید پرداخت (callback موفق درگاه) — قسط بسته و سفارش به‌روز می‌شود
     */
    public function complete(Request $request, Payment $payment)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($payment->user_id !== $user->id || $payment->status !== 'pending') {
            abort(404, 'چنین صفحه‌ای در سیستم وجود ندارد.');
        }

        $validated = $request->validate([
            'transaction_id' => ['nullable', 'string', 'max:100'],
        ]);

        $this->completePayment($payment, $validated['transaction_id'] ?? null);

        $order = $payment->order;

        return redirect()
            ->route('user.orders.show', $order->order_number)
            ->with('success', 'پرداخت قسط با موفقیت انجام شد.');
    }

    /**
     * عدم موفقیت پرداخت (callback ناموفق درگاه)
     */
    public function failed(Payment $payment)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($payment->user_id !== $user->id) {
            abort(404, 'چنین صفحه‌ای در سیستم وجود ندارد.');
        }

        $this->paymentService->fail($payment);

        return redirect()
            ->route('user.orders.show', $payment->order->order_number)
            ->with('error', 'پرداخت ناموفق بود — دوباره تلاش کنید.');
    }

    /**
     * تایید پرداخت — از طریق PaymentService مشترک + بستن قسط مرتبط
     *
     * قابل فراخوانی از callback درگاه و نیز تأیید دستی ادمین.
     */
    public function completePayment(Payment $payment, ?string $transactionId = null): void
    {
        // بستن قسط مرتبط — مبلغ قسط با پرداخت تاییدشده یکسان است
        // (قبل از complete تا_STATUS سفارش درست محاسبه شود)
        $order = $payment->order;

        OrderInstallment::where('order_id', $order->id)
            ->where('status', 'unpaid')
            ->where('amount', '<=', (float) $payment->amount + 0.01)
            ->orderBy('installment_number')
            ->first()
            ?->update(['status' => 'paid']);

        // تأیید پرداخت + به‌روزرسانی سفارش از طریق سرویس مشترک
        $this->paymentService->complete($payment, $transactionId);
    }
}
