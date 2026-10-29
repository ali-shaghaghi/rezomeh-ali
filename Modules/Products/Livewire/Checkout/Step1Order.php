<?php

namespace Modules\Products\Livewire\Checkout;

use Livewire\Component;
use Modules\Products\Enums\LicenseType;
use Modules\Products\Services\CheckoutService;

/**
 * مرحله ۱ — بازبینی سبد + کد تخفیف + انتخاب نوع لایسنس هر آیتم
 */
class Step1Order extends Component
{
    public string $discountCode = '';

    public string $errorMessage = '';

    public function mount(): void
    {
        $state = app(CheckoutService::class)->state();
        $this->discountCode = $state['discount_code'] ?? '';
    }

    public function updatedDiscountCode(string $value): void
    {
        $this->errorMessage = '';
    }

    /**
     * تغییر نوع لایسنس یک آیتم
     */
    public function setLicense(int $itemId, string $licenseType): void
    {
        $service = app(CheckoutService::class);

        try {
            $service->updateItemLicense($itemId, $licenseType);
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function applyDiscount(): void
    {
        $service = app(CheckoutService::class);

        try {
            if (trim($this->discountCode) === '') {
                $service->removeDiscount();
                $this->errorMessage = '';

                return;
            }

            $service->applyDiscount($this->discountCode);
            $this->errorMessage = '';
            $this->dispatch('discount-applied', percent: $service->totals()['discount_percent']);
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function removeDiscount(): void
    {
        app(CheckoutService::class)->removeDiscount();
        $this->discountCode = '';
        $this->errorMessage = '';
    }

    /**
     * رفتن به مرحله بعد
     */
    public function nextStep(): void
    {
        $this->redirect(route('checkout.step2'), navigate: true);
    }

    public function render()
    {
        $service = app(CheckoutService::class);

        return view('products::livewire.checkout.step1-order', [
            'items' => $service->items(),
            'totals' => $service->totals(),
            'licenseTypes' => LicenseType::cases(),
            'step' => 1,
        ]);
    }
}
