<?php

namespace Modules\Orders\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Orders\Models\Order;
use Modules\Products\Services\DownloadService;

/**
 * سرویس سفارش‌های «پنل کاربر» — فقط داده‌های خودِ کاربر لاگین‌شده
 * (منطق از UserPanelService به ماژول Orders منتقل شد).
 */
class UserOrderService
{
    /**
     * سفارش‌های کاربر — صفحه‌بندی‌شده + جستجو + فیلتر وضعیت
     */
    public function orders(User $user, int $perPage = 9, ?string $search = null, ?string $status = null): LengthAwarePaginator
    {
        $query = Order::where('user_id', $user->id);

        if ($search !== null && $search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        return $query->latest()->latest('id')->paginate($perPage)->withQueryString();
    }

    /**
     * سفارشِ خودِ کاربر یا null — شامل آیتم‌های محصولی و لایسنس‌های هر آیتم
     */
    public function order(User $user, string $orderNumber): ?Order
    {
        $order = Order::where('user_id', $user->id)
            ->where('order_number', $orderNumber)
            ->with(['payments', 'tasks', 'installments', 'items.product', 'items.licenses'])
            ->first();

        if (! $order) {
            return null;
        }

        // لینک دانلود امضاشده برای هر لایسنس محصولِ قابل دانلود (مثل «محصولات من»)
        $downloadService = app(DownloadService::class);

        foreach ($order->items as $item) {
            $item->setRelation('licenses', $item->licenses->sortByDesc('id')->values());

            foreach ($item->licenses as $license) {
                $license->download_url = $item->product?->is_downloadable
                    ? $downloadService->signedUrl($user, $item->product, $license)
                    : null;
            }
        }

        return $order;
    }
}
