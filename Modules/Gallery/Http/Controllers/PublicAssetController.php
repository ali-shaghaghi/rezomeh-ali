<?php

declare(strict_types=1);

namespace Modules\Gallery\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Modules\Gallery\Models\Asset;
use Modules\Gallery\Services\GalleryService;

class PublicAssetController extends Controller
{
    public function show(string $hash)
    {
        $asset = Asset::where('public_hash', $hash)->first();

        if ($asset === null) {
            abort(404);
        }

        if ($asset->uploader_path === null) {
            abort(404);
        }

        // فایل‌ها روی دیسک ذخیرهٔ گالری نگهداری می‌شوند و فقط از همین مسیر هش‌شده سرو می‌شوند
        $disk = GalleryService::DISK;

        if (!Storage::disk($disk)->exists($asset->uploader_path)) {
            abort(404);
        }

        // هدرها از همان ساخت پاس می‌شوند — متد header() روی BinaryFileResponse در Symfony جدید وجود ندارد
        return Response::file(
            Storage::disk($disk)->path($asset->uploader_path),
            [
                'Content-Type' => $asset->mime_type ?: 'application/octet-stream',
                'Cache-Control' => 'public, max-age=31536000, immutable',
            ]
        );
    }
}
