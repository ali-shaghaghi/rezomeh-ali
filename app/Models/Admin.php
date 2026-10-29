<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Core\Models\Role;

/**
 * مدل ادمین (کادر فنی) — جدول جدا از users با گارد جداگانه `admin`.
 *
 * قانون معماری: سطح دسترسی بالا فقط از این جدول می‌آید؛ جدول users هیچ‌گاه
 * نمی‌تواند ارتقا سطح پیدا کند.
 *
 * نکته: از User ارث می‌برد تا سیاست‌های Gate (که ورودی‌شان User است) و کد
 * موجود بدون تغییر روی ادمین هم کار کند — اما جدول و pivot نقش مجزا دارد.
 */
class Admin extends User
{
    protected $table = 'admins';

    /**
     * مقادیر پیش‌فرض ویژگی‌ها (جایگزین مقادیر پیش‌فرض User می‌شود).
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'two_factor_enabled' => false,
        'is_active' => true,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'login_route_token',
        'email_verified_at',
        'phone_verified_at',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'last_login_at',
        'last_login_ip',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'login_route_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * نقش‌های ادمین از pivot مخصوص `admin_role` می‌آیند — نه user_role.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'admin_role');
    }

    /**
     * ادمین همیشه ادمین است — سطح دسترسی از جدول خودش می‌آید نه از users.
     */
    public function getIsAdminAttribute(): bool
    {
        return true;
    }
}
