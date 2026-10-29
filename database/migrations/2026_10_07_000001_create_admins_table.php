<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * جدول جداگانه ادمین‌ها (کادر فنی/مدیریتی سایت).
     *
     * قانون معماری: جدول users هرگز نباید حاوی حساب دارای سطح دسترسی بالا باشد.
     * ادمین/مشاور/ویرایشگر/پشتیبانی از این پس فقط از این جدول خوانده می‌شوند
     * و با گارد مجزای `admin` احراز هویت می‌شوند.
     *
     * حساب‌های موجود کادر فنی که قبلاً در جدول users بودند به اینجا منتقل و
     * از جدول users حذف می‌شوند (ردیف‌های وابسته به آن‌ها با cascade پاک می‌شوند —
     * محتوای دمو با db:seed دوباره ساخته می‌شود).
     */
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->string('phone', 20)->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('password');
            $table->boolean('two_factor_enabled')->default(false);
            $table->string('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('admin_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['admin_id', 'role_id']);
        });

        $this->moveStaffAccountsToAdmins();
    }

    /**
     * انتقال حساب‌های کادر فنی: هر کاربری که نقشی غیر از «کاربر» دارد
     * به جدول admins منتقل و نقش‌هایش در admin_role کپی می‌شود.
     */
    private function moveStaffAccountsToAdmins(): void
    {
        $staffRoleIds = DB::table('roles')
            ->where('slug', '!=', 'user')
            ->pluck('id');

        if ($staffRoleIds->isEmpty()) {
            return;
        }

        $staffUserIds = DB::table('user_role')
            ->whereIn('role_id', $staffRoleIds)
            ->distinct()
            ->pluck('user_id');

        foreach ($staffUserIds as $userId) {
            $user = DB::table('users')->where('id', $userId)->first();

            if (! $user) {
                continue;
            }

            $adminId = DB::table('admins')->insertGetId([
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'email_verified_at' => $user->email_verified_at,
                'phone_verified_at' => $user->phone_verified_at,
                'password' => $user->password,
                'two_factor_enabled' => $user->two_factor_enabled ?? false,
                'two_factor_secret' => $user->two_factor_secret,
                'two_factor_recovery_codes' => $user->two_factor_recovery_codes,
                'last_login_at' => $user->last_login_at,
                'last_login_ip' => $user->last_login_ip,
                'is_active' => $user->is_active ?? true,
                'created_at' => $user->created_at ?? now(),
                'updated_at' => $user->updated_at ?? now(),
            ]);

            // کپی نقش‌ها به pivot مخصوص ادمین‌ها
            $roleRows = DB::table('user_role')
                ->where('user_id', $userId)
                ->whereIn('role_id', $staffRoleIds)
                ->get();

            foreach ($roleRows as $row) {
                DB::table('admin_role')->insert([
                    'admin_id' => $adminId,
                    'role_id' => $row->role_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // حذف ردیف کاربر قدیمی — user_role با cascade پاک می‌شود
            DB::table('users')->where('id', $userId)->delete();
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_role');
        Schema::dropIfExists('admins');
    }
};
