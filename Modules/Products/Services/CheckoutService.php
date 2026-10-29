<?php

namespace Modules\Products\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Modules\Orders\Enums\PaymentMethod;
use Modules\Orders\Models\Order;
use Modules\Orders\Services\OrderService;
use Modules\Orders\Services\PaymentService;
use Modules\Products\Enums\LicenseType;
use Modules\Products\Models\OrderItem;
use Modules\Products\Models\Product;

/**
 * Checkout ۴ مرحله‌ای — state در session نگه‌داری می‌شود:
 *   مرحله ۱: بازبینی سبد + کد تخفیف + نوع لایسنس
 *   مرحله ۲: اطلاعات فردی (پیش‌پر از پروفایل)
 *   مرحله ۳: جمع + مالیات ۹٪ + تخفیف + روش پرداخت
 *   مرحله ۴: پرداخت (درگاه / اقساطی / دستی) → ایجاد Order + OrderItem + License
 *
 * پرداخت از طریق PaymentService مشترک (Orders) انجام می‌شود.
 */
class CheckoutService
{
    private const SESSION_KEY = 'checkout';

    public function __construct(
        private readonly CartService $cartService,
        private readonly OrderService $orderService,
        private readonly LicenseService $licenseService,
        private readonly PaymentService $paymentService,
    ) {}

    /**
     * state کامل checkout (در صورت نبودن، از سبد مقداردهی اولیه می‌شود)
     */
    public function state(): array
    {
        $state = Session::get(self::SESSION_KEY, []);

        if (empty($state)) {
            $state = $this->freshState();
        }

        return $state;
    }

    /**
     * ساخت state اولیه از روی سبد فعلی + پروفایل کاربر
     */
    private function freshState(): array
    {
        $cart = $this->cartService->cart();
        $user = auth()->user();

        $state = [
            'step' => 1,
            'items' => [],
            'discount_code' => '',
            'personal' => [
                'name' => $user?->name ?? '',
                'phone' => $user?->phone ?? '',
                'email' => $user?->email ?? '',
                'notes' => '',
            ],
            'payment_method' => 'gateway',
        ];

        if ($cart) {
            foreach ($cart->items as $item) {
                $state['items'][$item->id] = [
                    'product_id' => $item->product_id,
                    'license_type' => $item->license_type,
                    'quantity' => $item->quantity,
                ];
            }
        }

        return $state;
    }

    /**
     * ذخیره state در session
     */
    private function save(array $state): array
    {
        Session::put(self::SESSION_KEY, $state);

        return $state;
    }

    /**
     * شروع checkout — اگر سبد خالی باشد exception پرتاب می‌شود.
     * idempotent: اگر state از قبل وجود دارد حفظ می‌شود (روش پرداخت، اطلاعات فردی،
     * کد تخفیف…) و فقط آیتم‌ها از سبد فعلی بازسازی می‌شوند — قبلاً هر بار که کاربر
     * صفحه هر مرحله را باز می‌کرد کل state صفر می‌شد و مثلاً روش پرداخت به «درگاه»
     * برمی‌گشت و شماره تلفن ثبت‌شده در مرحله ۲ فراموش می‌شد.
     */
    public function start(): array
    {
        if ($this->cartService->isEmpty()) {
            throw new \RuntimeException(__('shop.cart_empty').'.');
        }

        $existing = Session::get(self::SESSION_KEY, []);

        if ($existing !== []) {
            // آیتم‌ها همیشه از سبد فعلی — بقیه state کاربر حفظ می‌شود
            $existing['items'] = $this->freshState()['items'];

            return $this->save($existing);
        }

        return $this->save($this->freshState());
    }

    /**
     * گام فعلی
     */
    public function step(): int
    {
        return (int) ($this->state()['step'] ?? 1);
    }

    /**
     * تنظیم گام (۱ تا ۴)
     */
    public function setStep(int $step): array
    {
        $step = max(1, min(4, $step));
        $state = $this->state();
        $state['step'] = $step;

        return $this->save($state);
    }

    /**
     * پاک کردن state checkout
     */
    public function clearState(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    // ===== مرحله ۱: سبد + لایسنس + تخفیف =====

    /**
     * به‌روزرسانی نوع لایسنس یک آیتم در مرحله ۱
     */
    public function updateItemLicense(int $itemId, string $licenseType): array
    {
        $license = LicenseType::tryFrom($licenseType) ?? LicenseType::Single;

        $cart = $this->cartService->cart();
        if (! $cart || ! $cart->items()->whereKey($itemId)->exists()) {
            throw new \RuntimeException(__('shop.item_not_found'));
        }

        $product = Product::findOrFail($this->state()['items'][$itemId]['product_id']);

        $state = $this->state();
        $state['items'][$itemId]['license_type'] = $license->value;

        // سبد واقعی را هم همگام نگه می‌داریم تا قیمت نهایی درست محاسبه شود
        $cart->items()->whereKey($itemId)->update([
            'license_type' => $license->value,
            'unit_price' => $product->priceForLicense($license->value),
        ]);

        return $this->save($state);
    }

    /**
     * اعمال کد تخفیف
     */
    public function applyDiscount(string $code): array
    {
        $code = trim(strtoupper($code));
        $discounts = (array) config('products.discounts', []);

        if (! isset($discounts[$code])) {
            throw new \RuntimeException(__('shop.invalid_discount'));
        }

        $state = $this->state();
        $state['discount_code'] = $code;

        return $this->save($state);
    }

    /**
     * حذف کد تخفیف
     */
    public function removeDiscount(): array
    {
        $state = $this->state();
        $state['discount_code'] = '';

        return $this->save($state);
    }

    // ===== مرحله ۲: اطلاعات فردی =====

    /**
     * ذخیره اطلاعات فردی
     */
    public function setPersonalInfo(array $info): array
    {
        $state = $this->state();
        $state['personal'] = array_merge($state['personal'] ?? [], $info);

        return $this->save($state);
    }

    // ===== مرحله ۳: صورت‌حساب + روش پرداخت =====

    /**
     * روش‌های پرداخت مجاز در این checkout:
     *  - ارز دیجیتال فقط وقتی آدرس کیف پول ثبت شده باشد
     *  - کارت به کارت فقط وقتی شماره کارت در تنظیمات ثبت شده باشد
     *  - اقساطی هرگز (فقط برای پروژه‌ها — ماژول ProjectDev)
     *
     * @return array<string, string> کلید => برچسب فارسی خام (برچسب نهایی در view با __())
     */
    public function availablePaymentMethods(): array
    {
        $methods = (array) config('products.payment_methods', []);

        if (! \Modules\Settings\Models\Setting::getBool('crypto_enabled', false)) {
            unset($methods['crypto']);
        }

        $cardNumber = trim((string) \Modules\Settings\Models\Setting::get('payment_card_number', ''));
        if ($cardNumber === '') {
            unset($methods['manual']);
        }

        unset($methods['installment']);

        return $methods;
    }

    /**
     * تنظیم روش پرداخت
     */
    public function setPaymentMethod(string $method): array
    {
        if (! array_key_exists($method, $this->availablePaymentMethods())) {
            throw new \RuntimeException(__('shop.invalid_payment_method'));
        }

        // پرداخت ارز دیجیتال فقط وقتی فعال است که ادمین آدرس کیف پول را تنظیم کرده باشد
        if ($method === 'crypto' && ! \Modules\Settings\Models\Setting::getBool('crypto_enabled', false)) {
            throw new \RuntimeException(__('shop.crypto_disabled'));
        }

        $state = $this->state();
        $state['payment_method'] = $method;

        return $this->save($state);
    }

    /**
     * آیتم‌های checkout به‌همراه مدل محصول
     */
    public function items(): array
    {
        $state = $this->state();
        $productIds = collect($state['items'])->pluck('product_id')->all();

        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        return collect($state['items'])
            ->map(fn (array $row, int $itemId) => array_merge($row, [
                'item_id' => $itemId,
                'product' => $products->get($row['product_id']),
                'unit_price' => $products->get($row['product_id'])?->priceForLicense($row['license_type']) ?? 0,
                'license_label' => LicenseType::tryFrom($row['license_type'])?->localizedLabel() ?? LicenseType::Single->localizedLabel(),
            ]))
            ->all();
    }

    /**
     * محاسبه صورت‌حساب — جمع + تخفیف + مالیات ۹٪
     */
    public function totals(): array
    {
        $state = $this->state();
        $discounts = (array) config('products.discounts', []);

        $subtotal = 0;
        foreach ($this->items() as $row) {
            $subtotal += $row['unit_price'] * ($row['quantity'] ?? 1);
        }

        $discountPercent = $state['discount_code'] ? (float) ($discounts[$state['discount_code']] ?? 0) : 0.0;
        $discountAmount = round($subtotal * $discountPercent / 100);
        $afterDiscount = $subtotal - $discountAmount;
        $taxPercent = (float) config('products.tax_percent', 9);
        $taxAmount = round($afterDiscount * $taxPercent / 100);
        $total = $afterDiscount + $taxAmount;

        return [
            'subtotal' => $subtotal,
            'discount_percent' => $discountPercent,
            'discount_amount' => $discountAmount,
            'after_discount' => $afterDiscount,
            'tax_percent' => $taxPercent,
            'tax_amount' => $taxAmount,
            'total' => $total,
            'discount_code' => $state['discount_code'] ?? '',
        ];
    }

    // ===== مرحله ۴: تکمیل خرید =====

    /**
     * تکمیل خرید — تراکنشی: Order + OrderItems + Payment + Licenses + خالی کردن سبد
     */
    public function complete(User $user): Order
    {
        if ($this->cartService->isEmpty()) {
            throw new \RuntimeException(__('shop.cart_empty').'.');
        }

        $state = $this->state();
        $totals = $this->totals();
        $method = $state['payment_method'] ?? 'gateway';

        // درگاه واقعی زرین‌پال → سفارش pending می‌ماند تا پس از تایید درگاه تکمیل شود
        $deferGateway = $method === 'gateway'
            && app(\Modules\Orders\Services\PaymentService::class)->activeDriver() === 'zarinpal';

        return DB::transaction(function () use ($user, $state, $totals, $method, $deferGateway) {
            // ۱) سفارش
            $order = $this->orderService->save(new Order, [
                'user_id' => $user->id,
                'title' => $this->orderTitle(),
                'description' => $this->orderDescription($state),
                'status' => ($method === 'manual' || $deferGateway) ? 'pending' : 'completed',
                'amount' => $totals['total'],
                'paid_amount' => ($method === 'manual' || $deferGateway) ? 0 : $totals['total'],
            ]);

            // ۲) آیتم‌های سفارش + صدور لایسنس به ازای هر واحد محصول
            foreach ($this->items() as $row) {
                /** @var Product|null $product */
                $product = $row['product'];
                if (! $product) {
                    continue;
                }

                $quantity = (int) ($row['quantity'] ?? 1);
                $unitPrice = (float) $row['unit_price'];
                $type = LicenseType::tryFrom($row['license_type']) ?? LicenseType::Single;

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'license_type' => $type->value,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $unitPrice * $quantity,
                ]);

                // مسیر زرین‌پال: لایسنس بعد از تایید درگاه صادر می‌شود (PaymentService::onPaymentCompleted)
                if (! $deferGateway) {
                    for ($i = 0; $i < $quantity; $i++) {
                        $this->licenseService->issue($user, $product, $type, $orderItem->id);
                    }
                }
            }

            // ۳) ثبت پرداخت از طریق PaymentService مشترک
            $paymentMethod = PaymentMethod::fromValue($method);

            $payment = $this->paymentService->create($order, [
                'amount' => $totals['total'],
                'method' => $paymentMethod,
                'description' => 'خرید محصول — کد تخفیف: '.($totals['discount_code'] ?: 'بدون تخفیف'),
            ]);

            // پرداخت آنلاین → تأیید فوری از طریق درگاه مشترک
            // (در مسیر زرین‌پال واقعی صبر می‌شود تا callback درگاه تایید کند)
            if ($paymentMethod === PaymentMethod::Gateway && ! $deferGateway) {
                $this->paymentService->complete($payment);
            }

            // پرداخت اقساطی: ۳۰/۳۰/۴۰ + بستن قسط پیش‌پرداخت
            if ($paymentMethod === PaymentMethod::Installment) {
                $this->paymentService->complete($payment);
                $this->createInstallments($order, $payment->transaction_id);
            }

            // ۴) خالی کردن سبد + state
            $this->cartService->clear();
            $this->clearState();

            return $order->refresh();
        });
    }

    /**
     * ایجاد اقساط ۳۰/۳۰/۴۰ برای سفارش
     */
    private function createInstallments(Order $order, ?string $transactionId): void
    {
        $total = (float) $order->amount;
        if ($total <= 0) {
            return;
        }

        $percents = [30, 30, 40];

        foreach ($percents as $i => $percent) {
            \Modules\ProjectDev\Models\OrderInstallment::create([
                'order_id' => $order->id,
                'installment_number' => $i + 1,
                'title' => ['پیش‌پرداخت', 'قسط دوم', 'قسط سوم'][$i],
                'amount' => round($total * $percent / 100),
                'percent' => $percent,
                'due_date' => $i === 0 ? now() : now()->addMonths($i),
                'status' => $i === 0 ? 'paid' : 'unpaid',
            ]);
        }
    }

    private function orderTitle(): string
    {
        return 'خرید محصول ('.count($this->items()).' مورد)';
    }

    private function orderDescription(array $state): string
    {
        $names = collect($this->items())
            ->map(fn ($row) => ($row['product']?->name ?? 'محصول').' × '.$row['quantity'])
            ->implode('، ');

        $p = $state['personal'] ?? [];

        return $names."\n".
            'نام: '.($p['name'] ?? '-').' | تلفن: '.($p['phone'] ?? '-').' | ایمیل: '.($p['email'] ?? '-').
            (empty($p['notes']) ? '' : "\nتوضیحات: ".$p['notes']);
    }
}

