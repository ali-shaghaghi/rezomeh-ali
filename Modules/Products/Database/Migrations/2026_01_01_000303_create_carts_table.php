<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // مایگریشن ماژول محصولات — سبد خرید (session-based)
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index(); // شناسه سشن کاربر مهمان
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('license_type')->default('single'); // single, multi, lifetime
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0); // قیمت ثبت‌شده هنگام افزودن
            $table->timestamps();

            // هر محصول با یک نوع لایسنس فقط یک بار در سبد
            $table->unique(['cart_id', 'product_id', 'license_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
