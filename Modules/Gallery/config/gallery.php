<?php

declare(strict_types=1);

return [
    'disks' => [
        'public' => 'gallery_public',
        'private' => 'upload_private',
    ],

    'public_disk' => env('GALLERY_PUBLIC_DISK', 'gallery_public'),
    'private_disk' => env('GALLERY_PRIVATE_DISK', 'upload_private'),

    'upload' => [
        'max_files_per_upload' => env('GALLERY_MAX_UPLOADS', 12),
        'max_bytes_per_image' => env('GALLERY_MAX_IMAGE_BYTES', 8 * 1024 * 1024),
        'max_bytes_per_video' => env('GALLERY_MAX_VIDEO_BYTES', 50 * 1024 * 1024),
    ],

    'storage_path' => storage_path('app/gallery'),

    'public_path' => storage_path('app/public/gallery'),

    'upload_path' => storage_path('app/gallery/uploads'),

    'urls' => [
        'public_asset_url' => env('GALLERY_PUBLIC_ASSET_URL', null),
        'public_asset_path' => env('GALLERY_PUBLIC_ASSET_PATH', '/gallery'),
    ],

    'hashing' => [
        'algorithm' => 'sha256',
        'seed_length' => 12,
    ],

    'placeholder' => [
        'url' => '/img/gallery-placeholder.svg',
        'alt' => 'Gallery placeholder',
    ],

    'thumbs' => [
        'width' => 400,
        'height' => 400,
        'fit' => 'crop',
    ],

    'cleanup' => [
        'enabled' => env('GALLERY_CLEANUP_ENABLED', true),
        'orphan_expiry_days' => 14,
        'processed_expiry_days' => null,
        'delete_assets' => true,
    ],
];
