<?php

namespace Modules\Products\Livewire\Checkout;

use Livewire\Component;
use Modules\Products\Services\CheckoutService;

/**
 * مرحله ۳ — جمع + مالیات ۹٪ + تخفیف + روش پرداخت
 */
class Step3Invoice extends Component
{
    public string $paymentMethod = 'gateway';

    public string $errorMessage = '';

    public function mount(): void
    {
        $state = app(CheckoutService::class)->state();
        $this->paymentMethod = $state['payment_method'] ?? 'gateway';

        // روش نامعتبرِ در state (مثلاً اقساطی که حذف شده) → بازگشت به درگاه
        if (! array_key_exists($this->paymentMethod, app(CheckoutService::class)->availablePaymentMethods())) {
            $this->paymentMethod = 'gateway';
        }
    }

    public function updatedPaymentMethod(string $value): void
    {
        try {
            app(CheckoutService::class)->setPaymentMethod($value);
            $this->errorMessage = '';
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function selectMethod(string $method): void
    {
        $this->paymentMethod = $method;
        $this->updatedPaymentMethod($method);
    }

    public function nextStep(): void
    {
        try {
            app(CheckoutService::class)->setPaymentMethod($this->paymentMethod);
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();

            return;
        }

        $this->redirect(route('checkout.step4'), navigate: true);
    }

    public function prevStep(): void
    {
        $this->redirect(route('checkout.step2'), navigate: true);
    }

    public function render()
    {
        $service = app(CheckoutService::class);

        // روش‌های پرداخت مجاز (crypto با فعال‌سازی کیف پول، کارت به کارت با ثبت شماره کارت — بدون اقساطی)
        $methods = collect(app(CheckoutService::class)->availablePaymentMethods())
            ->map(fn ($label, $key) => __('shop.payment_methods.'.$key))
            ->all();

        return view('products::livewire.checkout.step3-invoice', [
            'items' => $service->items(),
            'totals' => $service->totals(),
            'paymentMethods' => $methods,
            'step' => 3,
        ]);
    }
}
