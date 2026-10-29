<?php

declare(strict_types=1);

namespace Modules\Gallery\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Gallery\Models\Asset;

class GalleryService
{
    public const DISK = 'upload_private';

    public const MAX_FILES_PER_UPLOAD = 12;

    public const MAX_BYTES_PER_IMAGE = 8 * 1024 * 1024;

    /**
     * خواندن تصاویر با فیلتر، مرتب‌سازی و صفحه‌بندی.
     */
    public function assets(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Asset::query();

        if (($slug = $filters['slug'] ?? null) !== null) {
            $query->where('slug', $slug);
        }

        if (($search = $filters['search'] ?? null) !== null && $search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('alt', 'like', "%{$search}%")
                    ->orWhere('meta_title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if (($type = $filters['type'] ?? null) !== null && $type !== 'all') {
            $query->where('is_video', $type === 'video');
        }

        if (($source = $filters['source_type'] ?? null) !== null) {
            $query->where('source_type', $source);

            if (($sourceId = $filters['source_id'] ?? null) !== null) {
                $query->where('source_id', $sourceId);
            }
        }

        // فقط منتشرشده‌ها (دارای public_hash) مگر اینکه صراحتاً خواسته شود
        if (! ($filters['published_only'] ?? false)) {
            $query->whereNotNull('public_hash');
        }

        match ($filters['sort'] ?? 'newest') {
            'title' => $query->orderBy('meta_title', 'asc'),
            'oldest' => $query->orderBy('created_at', 'asc'),
            'az' => $query->orderBy('slug', 'asc'),
            default => $query->orderBy('created_at', 'desc'),
        };

        return $query->paginate($perPage);
    }

    public function asset(string $slug): ?Asset
    {
        return Asset::where('slug', $slug)->first();
    }

    public function assetByHash(string $hash): ?Asset
    {
        return Asset::where('public_hash', $hash)->first();
    }

    public function assetById(int $id): ?Asset
    {
        return Asset::find($id);
    }

    /**
     * تصاویر منتشرشده برای انتخاب در ویرایشگر متن (فرم مقاله/محصول).
     *
     * @return \Illuminate\Support\Collection<int, Asset>
     */
    public function publishedAssets(int $limit = 300)
    {
        return Asset::query()
            ->whereNotNull('public_hash')
            ->where('is_video', false)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * ساخت رکورد گالری از فایل آپلودشده — فایل روی دیسک خصوصی ذخیره می‌شود
     * و فقط از مسیر عمومی هش‌شده (/gallery/{hash}) سرو می‌شود.
     */
    public function createFromUploadedFile(UploadedFile $file, array $meta = []): Asset
    {
        $path = $file->store('uploads', self::DISK);

        [$width, $height] = $this->imageDimensions($file);

        $originalName = $meta['original_name'] ?? $file->getClientOriginalName();

        return Asset::create([
            'slug' => $meta['slug'] ?? $this->uniqueSlug($originalName),
            'alt' => $meta['alt'] ?? null,
            'meta_title' => $meta['meta_title'] ?? $this->titleFromName($originalName),
            'meta_description' => $meta['meta_description'] ?? null,
            'public_hash' => $this->hash(),
            'uploader_path' => $path,
            'original_name' => $originalName,
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'width' => $width,
            'height' => $height,
            'is_video' => str_starts_with((string) $file->getMimeType(), 'video/'),
            'is_processed' => true,
            'source_id' => $meta['source_id'] ?? null,
            'source_type' => $meta['source_type'] ?? null,
        ]);
    }

    /**
     * حذف کامل یک تصویر: فایل از دیسک + ردیف از دیتابیس.
     */
    public function deleteAsset(Asset $asset): void
    {
        if ($asset->uploader_path !== null && Storage::disk(self::DISK)->exists($asset->uploader_path)) {
            Storage::disk(self::DISK)->delete($asset->uploader_path);
        }

        $asset->delete();
    }

    public function hash(): string
    {
        return hash('sha256', Str::random(24));
    }

    /**
     * ابعاد تصویر (برای رزرو فضا در صفحه و سئو) — برای غیرتصویر null.
     *
     * @return array{0: ?int, 1: ?int}
     */
    private function imageDimensions(UploadedFile $file): array
    {
        if (! str_starts_with((string) $file->getMimeType(), 'image/')) {
            return [null, null];
        }

        $info = @getimagesize($file->getRealPath());

        if ($info === false) {
            return [null, null];
        }

        return [(int) $info[0], (int) $info[1]];
    }

    /**
     * عنوان سئو پیش‌فرض از نام فایل.
     */
    private function titleFromName(string $name): string
    {
        $base = pathinfo($name, PATHINFO_FILENAME);

        return Str::limit(trim(preg_replace('/[-_]+/u', ' ', $base) ?? $base), 200, '');
    }

    /**
     * slug یکتا و سازگار با سئو — برای نام فارسی به fallback هوشمند برمی‌گردد.
     */
    private function uniqueSlug(string $value): string
    {
        $slug = Str::slug($value);

        if ($slug === '') {
            // نام فارسی/غیرلاتین: برای سئو بهتر است از alt استفاده شود؛ در نبودش شناسهٔ کوتاه
            $slug = 'image-' . Str::lower(Str::random(8));
        }

        $base = $slug;
        $candidate = $base;
        $index = 0;

        while (Asset::where('slug', $candidate)->exists()) {
            $index++;
            $candidate = $base . '-' . $index;
        }

        return $candidate;
    }
}
