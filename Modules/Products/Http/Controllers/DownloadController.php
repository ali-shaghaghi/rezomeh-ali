<?php

namespace Modules\Products\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Modules\Products\Models\Product;
use Modules\Products\Services\DownloadService;
use Modules\Products\Services\LicenseService;

class DownloadController extends Controller
{
    public function __construct(
        private readonly DownloadService $downloadService,
        private readonly LicenseService $licenseService,
    ) {}

    /**
     * دانلود محصول با لینک امقاشده (۲ ساعت معتبر).
     * لاگ دانلود هنگام ساخت لینک ثبت می‌شود.
     */
    public function download(Request $request, string $slug)
    {
        // اعتبارسنجی امضای لینک (زمان + مسیر)
        if (! $request->hasValidSignature()) {
            abort(403, 'لینک دانلود نامعتبر یا منقضی شده است.');
        }

        $product = Product::published()->where('slug', $slug)->firstOrFail();

        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user || ! $this->downloadService->hasAccess($user, $product)) {
            abort(403, 'شما لایسنس معتبری برای این محصول ندارید.');
        }

        // ثبت لاگ دانلود (با IP + User Agent) — فقط هنگام دانلود واقعی
        $license = $user ? \Modules\Products\Models\License::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->latest('id')
            ->first() : null;

        $this->downloadService->log($user, $product, $license, $request);

        return $this->downloadService->stream($product);
    }
}
