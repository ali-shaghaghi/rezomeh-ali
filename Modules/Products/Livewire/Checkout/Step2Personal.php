<?php

namespace Modules\Products\Livewire\Checkout;

use Livewire\Component;
use Modules\Products\Services\CheckoutService;

/**
 * مرحله ۲ — اطلاعات فردی (پیش‌پر از پروفایل کاربر)
 */
class Step2Personal extends Component
{
    public string $name = '';

    public string $phone = '';

    public string $email = '';

    public string $notes = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'phone' => ['required', 'string', 'regex:/^09\d{9}$/'],
            'email' => ['nullable', 'email', 'max:190'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected $validationAttributes = [
        'name' => 'نام و نام خانوادگی',
        'phone' => 'شماره تلفن',
        'email' => 'ایمیل',
        'notes' => 'توضیحات',
    ];

    public function mount(): void
    {
        $personal = app(CheckoutService::class)->state()['personal'] ?? [];

        $user = auth()->user();

        $this->fill([
            'name' => $personal['name'] ?? ($user?->name ?? ''),
            'phone' => $personal['phone'] ?? ($user?->phone ?? ''),
            'email' => $personal['email'] ?? ($user?->email ?? ''),
            'notes' => $personal['notes'] ?? '',
        ]);
    }

    public function nextStep(): void
    {
        $validated = $this->validate();

        app(CheckoutService::class)->setPersonalInfo($validated);

        $this->redirect(route('checkout.step3'), navigate: true);
    }

    public function prevStep(): void
    {
        // ذخیره موقت اطلاعات قبل از بازگشت
        app(CheckoutService::class)->setPersonalInfo([
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'notes' => $this->notes,
        ]);

        $this->redirect(route('checkout.step1'), navigate: true);
    }

    public function render()
    {
        return view('products::livewire.checkout.step2-personal', [
            'step' => 2,
            'items' => app(CheckoutService::class)->items(),
        ]);
    }
}
