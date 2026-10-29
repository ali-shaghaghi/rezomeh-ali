<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_assets', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->nullable()->index();
            $table->string('alt')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('public_hash', 64)->nullable()->unique();
            $table->string('uploader_path')->nullable();
            $table->string('original_name')->nullable();
            $table->string('mime_type', 128)->nullable();
            $table->unsignedInteger('size_bytes')->nullable();
            $table->unsignedSmallInteger('width')->nullable();
            $table->unsignedSmallInteger('height')->nullable();
            $table->unsignedInteger('order_column')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->boolean('is_video')->default(false);
            $table->boolean('is_processed')->default(true);
            $table->unsignedInteger('uploaded_by')->nullable();
            $table->unsignedInteger('source_id')->nullable();
            $table->string('source_type', 64)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['source_type', 'source_id']);
            $table->index(['is_video', 'is_processed']);
            $table->index(['order_column']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_assets');
    }
};
