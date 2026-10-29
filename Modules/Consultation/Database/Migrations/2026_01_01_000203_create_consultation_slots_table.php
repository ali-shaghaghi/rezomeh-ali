<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // اسلات‌های تولیدشده از زمان‌بندی — قابل رزرو
    public function up(): void
    {
        Schema::create('consultation_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultant_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_booked')->default(false);
            $table->timestamp('booked_at')->nullable();
            $table->timestamps();

            $table->unique(['consultant_id', 'date', 'start_time']);
            $table->index(['consultant_id', 'date', 'is_booked']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_slots');
    }
};