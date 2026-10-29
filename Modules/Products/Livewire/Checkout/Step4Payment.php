<?php

namespace Modules\Products\Livewire\Checkout;

use Livewire\Component;
use Modules\Products\Services\CheckoutService;

/**
 * مرحله ۴ — پرداخت: درگاه آنلاین / کارت به کارت (فقط با ثبت شماره کارت) / ارز دیجیتال
 * اقساطی فقط برای پروژه‌هاست و از این checkout حذف شده.
 * پس از تکمیل: Order + OrderItem + License ساخته می‌شود، سبد خالی
 * و کاربر به /dashboard/products هدایت می‌شود.
 */
class Step4Payment extends Component
{
    public string $paymentMethod = 'gateway';

    public string $errorMessage = '';

    public bool $processing = false;

    public function mount(): void
    {
        $state = app(CheckoutService::class)->state();
        $this->paymentMethod = $state['payment_method'] ?? 'gateway';

        // روش نامعتبرِ در state (مثلاً اقساطی حذف‌شده یا کارت به کارت بدون شماره کارت) → درگاه
        if (! array_key_exists($this->paymentMethod, app(CheckoutService::class)->availablePaymentMethods())) {
            $this->paymentMethod = 'gateway';
        }
    }

    /**
     * پردازش پرداخت و تکمیل خرید
     */
    public function pay(): void
    {
        $this->processing = true;
        $this->errorMessage = '';

        try {
            $service = app(CheckoutService::class);
            $service->setPaymentMethod($this->paymentMethod);

            $order = $service->complete(auth()->user());

            // درگاه واقعی زرین‌پال → هدایت به صفحه پرداخت زرین‌پال (سندباکس یا واقعی)
            $paymentService = app(\Modules\Orders\Services\PaymentService::class);
            $payment = $order->payments()->latest('id')->first();

            if ($payment
                && $payment->status === 'pending'
                && $this->paymentMethod === 'gateway'
                && $paymentService->activeDriver() === 'zarinpal') {
                // URL خارجی (sandbox.zarinpal.com یا payment.zarinpal.com)
                $this->redirect($paymentService->zarinpalRequest($payment));

                return;
            }

            // موفقیت → داشبورد محصولات با پیام
            session()->flash('success', 'خرید شما با موفقیت تکمیل شد! شماره سفارش: '.$order->order_number);

            $this->redirect(route('user.products.index'), navigate: true);
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $this->processing = false;
        }
    }

    public function render()
    {
        $service = app(CheckoutService::class);

        $methods = collect($service->availablePaymentMethods())
            ->map(fn ($label, $key) => __('shop.payment_methods.'.$key))
            ->all();

        $totals = $service->totals();

        return view('products::livewire.checkout.step4-payment', [
            'items' => $service->items(),
            'totals' => $totals,
            'paymentMethods' => $methods,
            'cryptoWallet' => (string) \Modules\Settings\Models\Setting::get('crypto_wallet_address', ''),
            'cryptoNetwork' => (string) \Modules\Settings\Models\Setting::get('crypto_network', 'trc20'),
            'cryptoCoins' => $this->cryptoCoins((float) $totals['total']),
            'cardNumber' => (string) \Modules\Settings\Models\Setting::get('payment_card_number', ''),
            'step' => 4,
        ]);
    }

    /**
     * رمزارزهای قابل انتخاب برای پرداخت — تتر + جایگزین‌ها (BTC/ETH/LTC/TRX)
     * هر کدام که آدرس (و برای جایگزین‌ها، نرخ) داشته باشند نمایش داده می‌شوند.
     *
     * @return array<int, array{key: string, name: string, network: string, address: string, amount: float|null, decimals: int}>
     */
    private function cryptoCoins(float $total): array
    {
        $setting = fn (string $key, string $default = '') => (string) \Modules\Settings\Models\Setting::get($key, $default);

        $coins = [];

        // تتر — معادل‌سازی با نرخ دلار (تب قیمت ارز)
        $usdtWallet = $setting('crypto_wallet_address');
        if ($usdtWallet !== '') {
            $usdRate = (float) \Modules\Settings\Models\Setting::get('usd_rate', 0);
            $coins[] = [
                'key' => 'usdt',
                'name' => 'USDT (Tether)',
                'network' => strtoupper($setting('crypto_network', 'trc20')),
                'address' => $usdtWallet,
                'amount' => $usdRate > 0 ? $total / $usdRate : null,
                'decimals' => 2,
            ];
        }

        // رمزارزهای جایگزین — آدرس + نرخ دستی (تومان به ازای هر واحد)
        foreach (['btc' => 'بیت‌کوین (BTC)', 'eth' => 'اتریوم (ETH)', 'ltc' => 'لایت‌کوین (LTC)', 'trx' => 'ترون (TRX)'] as $key => $label) {
            $address = $setting("crypto_{$key}_address");
            $rate = (float) \Modules\Settings\Models\Setting::get("crypto_{$key}_rate", 0);

            if ($address === '') {
                continue;
            }

            $coins[] = [
                'key' => $key,
                'name' => $label,
                'network' => strtoupper($key),
                'address' => $address,
                'amount' => $rate > 0 ? $total / $rate : null,
                'decimals' => in_array($key, ['btc', 'eth'], true) ? 6 : 2,
            ];
        }

        return $coins;
    }
}
