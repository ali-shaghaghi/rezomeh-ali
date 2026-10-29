<?php

namespace Modules\Products\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Modules\Products\Models\License;
use Modules\Products\Models\Product;

class DownloadService
{
    /**
     * ساخت لینک دانلود امضاشده با اعتبارسنجی زمانی
     *
     * @return string URL امقاشده (۲ ساعت معتبر)
     */
    public function signedUrl(User $user, Product $product, ?License $license = null): string
    {
        return URL::temporarySignedRoute(
            'user.products.download',
            now()->addHours((int) config('products.download_expiry_hours', 2)),
            ['slug' => $product->slug]
        );
    }

    /**
     * ثبت لاگ دانلود
     */
    public function log(User $user, Product $product, ?License $license = null, ?Request $request = null): void
    {
        $request ??= request();

        \Modules\Products\Models\Download::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'license_id' => $license?->id,
            'ip' => $request->ip(),
            'ua' => $request->userAgent(),
        ]);
    }

    /**
     * تعداد دانلودهای یک محصول
     */
    public function downloadCount(Product $product): int
    {
        return (int) \Modules\Products\Models\Download::where('product_id', $product->id)->count();
    }

    /**
     * آیا کاربر این محصول را خریده است (لایسنس معتبر دارد)؟
     */
    public function hasAccess(User $user, Product $product): bool
    {
        return License::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->exists();
    }

    /**
     * ارسال فایل محصول به کاربر (response دانلود)
     */
    public function stream(Product $product)
    {
        if (! $product->is_downloadable) {
            abort(404, 'فایل دانلودی برای این محصول وجود ندارد.');
        }

        return response()->download(storage_path('app/private/'.$product->file_path));
    }
}
