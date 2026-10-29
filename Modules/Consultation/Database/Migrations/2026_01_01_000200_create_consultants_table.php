<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // مشاوران — پروفایل کاری هر مشاور
    public function up(): void
    {
        Schema::create('consultants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('specialty', 150);
            $table->text('bio')->nullable();
            $table->string('avatar')->nullable();
            $table->decimal('hourly_rate', 12, 2)->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultants');
    }
};