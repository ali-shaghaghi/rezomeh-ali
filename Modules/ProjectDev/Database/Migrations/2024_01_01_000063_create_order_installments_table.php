<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Orders\Models\Order;

return new class extends Migration
{
    // ماژول Projects — قسط‌های پرداخت 30/30/40 روی سفارش (فاکتور نهایی)
    public function up(): void
    {
        Schema::create('order_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('installment_number'); // 1..n
            $table->string('title');
            $table->decimal('amount', 15, 2);
            $table->unsignedTinyInteger('percent');
            $table->timestamp('due_date')->nullable();
            $table->enum('status', ['unpaid', 'paid', 'canceled'])->default('unpaid');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_installments');
    }
};
