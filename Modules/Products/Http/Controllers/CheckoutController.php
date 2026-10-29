<?php

namespace Modules\Products\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Products\Services\CheckoutService;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CheckoutService $checkoutService,
    ) {}

    private function ensureStarted(): void
    {
        try {
            $this->checkoutService->start();
        } catch (\RuntimeException $e) {
            abort(redirect()->route('public.cart.index')->with('error', $e->getMessage()));
        }
    }

    /**
     * مرحله ۱ — بازبینی سبد + کد تخفیف + نوع لایسنس
     */
    public function step1()
    {
        $this->ensureStarted();

        return view('products::checkout.step1');
    }

    /**
     * مرحله ۲ — اطلاعات فردی (پیش‌پر از پروفایل)
     */
    public function step2()
    {
        $this->ensureStarted();

        return view('products::checkout.step2');
    }

    /**
     * مرحله ۳ — جمع + مالیات ۹٪ + تخفیف + روش پرداخت
     */
    public function step3()
    {
        $this->ensureStarted();

        return view('products::checkout.step3');
    }

    /**
     * مرحله ۴ — پرداخت (درگاه / اقساطی / دستی)
     */
    public function step4()
    {
        $this->ensureStarted();

        return view('products::checkout.step4');
    }
}
