<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id')->nullable();
            $table->string('page');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('last_active_at');
            $table->timestamps();

            $table->index(['user_id', 'last_active_at']);
            $table->index(['session_id', 'last_active_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};