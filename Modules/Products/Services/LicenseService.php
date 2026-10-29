<?php

namespace Modules\Products\Services;

use App\Models\User;
use Illuminate\Support\Str;
use Modules\Products\Enums\LicenseType;
use Modules\Products\Models\License;
use Modules\Products\Models\OrderItem;
use Modules\Products\Models\Product;

class LicenseService
{
    /**
     * صدور یک لایسنس جدید با کلید UUID یکتا
     */
    public function issue(User $user, Product $product, LicenseType $type, ?int $orderItemId = null): License
    {
        return License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_item_id' => $orderItemId,
            'license_key' => $this->generateKey(),
            'type' => $type->value,
            'max_activations' => $type->maxActivations(),
            'activations_count' => 0,
            'expires_at' => $type->validMonths() ? now()->addMonths($type->validMonths()) : null,
        ]);
    }

    /**
     * صدور لایسنس برای یک آیتم سفارش
     */
    public function issueForOrderItem(OrderItem $item): License
    {
        $product = $item->product;
        $type = LicenseType::tryFrom($item->license_type) ?? LicenseType::Single;

        $license = $this->issue($item->order->user, $product, $type, $item->id);

        // اعلان «محصول فروخته شد» (ماژول Notifications)
        try {
            app(\Modules\Notifications\Services\NotificationService::class)
                ->notifyUsersWithPermission(
                    'view_notifications',
                    \Modules\Notifications\Notifications\ProductSoldNotification::class,
                    [
                        'product_name' => $item->product_name,
                        'quantity' => (int) $item->quantity,
                        'revenue' => (float) $item->total_price,
                    ]
                );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Notification failed: '.$e->getMessage());
        }

        return $license;
    }

    /**
     * تولید کلید لایسنس یکتا (UUID v4 با خط تیره)
     */
    public function generateKey(): string
    {
        do {
            $key = Str::uuid()->toString();
        } while (License::where('license_key', $key)->exists());

        return $key;
    }

    /**
     * جستجوی لایسنس با کلید
     */
    public function findByKey(string $key): ?License
    {
        return License::with('product')->where('license_key', $key)->first();
    }

    /**
     * فعال‌سازی یک لایسنس (در صورت سقف آزاد بودن)
     */
    public function activate(License $license): bool
    {
        if (! $license->is_valid) {
            return false;
        }

        $updated = License::whereKey($license->id)
            ->where('activations_count', '<', $license->max_activations)
            ->increment('activations_count');

        if ($updated && $license->activated_at === null) {
            $license->update(['activated_at' => now()]);
        }

        return (bool) $updated;
    }

    /**
     * لایسنس‌های معتبر کاربر برای یک محصول
     */
    public function userLicensesForProduct(User $user, Product $product)
    {
        return License::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->orderByDesc('id')
            ->get();
    }

    /**
     * همه لایسنس‌های کاربر
     */
    public function userLicenses(User $user)
    {
        return License::query()
            ->with('product')
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->get();
    }
}
