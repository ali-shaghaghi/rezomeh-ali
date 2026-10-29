<?php

namespace Modules\Products\Livewire\UserPanel;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Products\Models\Download;
use Modules\Products\Models\License;
use Modules\Products\Services\DownloadService;
use Modules\Products\Services\LicenseService;

/**
 * محصولات من — کارت محصول + کلید لایسنس + دکمه دانلود
 */
class MyProducts extends Component
{
    use WithPagination;

    public function render()
    {
        $user = auth()->user();

        $licenses = License::query()
            ->with(['product.category', 'orderItem.order'])
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->paginate(9);

        $downloadService = app(DownloadService::class);

        // افزودن لینک دانلود امضاشده به هر لایسنس (۲ ساعت معتبر)
        $licenses->getCollection()->transform(function (License $license) use ($downloadService, $user) {
            $license->download_url = $license->product?->is_downloadable
                ? $downloadService->signedUrl($user, $license->product, $license)
                : null;

            return $license;
        });

        return view('products::livewire.user-panel.my-products', [
            'licenses' => $licenses,
            'totalDownloads' => Download::where('user_id', $user->id)->count(),
            'validLicenses' => License::where('user_id', $user->id)->valid()->count(),
        ]);
    }

    /**
     * کپی کلید لایسنس (فقط کلاينت‌ساید — handled in blade)
     */
    public function copyKey(int $licenseId): void
    {
        // کلید از طریق Alpine کپی می‌شود — این متد فقط برای ثبت رویداد است
    }
}
