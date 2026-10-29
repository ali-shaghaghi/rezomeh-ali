<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Modules\Core\Models\Role;

/**
 * کاربر عادی سایت (جدول users).
 *
 * قانون معماری: این مدل هیچ‌گاه ارتقا سطح پیدا نمی‌کند — نقش بالاتر از «کاربر»
 * نمی‌گیرد و اکسسور is_admin ندارد. ادمین/مشاور/ویرایشگر فقط در جدول admins.
 */
class User extends Authenticatable implements \Illuminate\Contracts\Auth\MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, MustVerifyEmail, Notifiable;

    /**
     * مقادیر پیش‌فرض ویژگی‌ها (پیش از ذخیره در دیتابیس نیز در دسترس‌اند).
     * قابلیت‌های امنیتی به‌صورت پیش‌فرض غیرفعال‌اند.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'two_factor_enabled' => false,
        'email_verification_enabled' => false,
        'login_otp_enabled' => false,
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
        'avatar',
        'social_provider',
        'social_id',
        'password',
        'phone_verified_at',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'email_verification_enabled',
        'login_otp_enabled',
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
            'email_verification_enabled' => 'boolean',
            'login_otp_enabled' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the roles for the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    /**
     * Check if the user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->roles->contains('slug', $role);
    }

    /**
     * Check if the user has any of the given roles.
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->roles->whereIn('slug', $roles)->isNotEmpty();
    }

    /**
     * Check if the user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        return $this->roles->flatMap->permissions->contains('slug', $permission);
    }

    /**
     * Check if the user has any of the given permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        return $this->roles->flatMap->permissions->whereIn('slug', $permissions)->isNotEmpty();
    }

    /**
     * Get the user's phone number masked.
     */
    public function getMaskedPhoneAttribute(): string
    {
        if (!$this->phone) {
            return '';
        }

        $length = strlen($this->phone);
        if ($length <= 4) {
            return $this->phone;
        }

        return substr($this->phone, 0, 3) . str_repeat('*', $length - 5) . substr($this->phone, -2);
    }
}
