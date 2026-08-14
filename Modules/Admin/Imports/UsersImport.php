<?php

namespace Modules\Admin\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Modules\Core\Models\Role;

class UsersImport implements ToCollection, WithHeadingRow
{
    /**
     * تعداد کاربران ایجاد شده
     */
    public int $created = 0;

    /**
     * تعداد کاربران به‌روزرسانی شده
     */
    public int $updated = 0;

    /**
     * تعداد ردیف‌های رد شده
     */
    public int $skipped = 0;

    /**
     * لیست خطاها
     */
    public array $errors = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $this->processRow($row);
        }
    }

    protected function processRow($row)
    {
        // خواندن ایمیل (پشتیبانی از هدر انگلیسی و فارسی)
        $email = $this->getValue($row, ['email', 'ایمیل']);

        // اگر ایمیل خالی بود، ردیف را رد کن
        if (empty($email)) {
            $this->skipped++;
            $name = $this->getValue($row, ['name', 'نام']) ?: 'نامشخص';
            $this->errors[] = "ردیف «{$name}»: ایمیل ندارد و ذخیره نشد.";
            return;
        }

        // خواندن سایر فیلدها
        $name = $this->getValue($row, ['name', 'نام']);
        $phone = $this->getValue($row, ['phone', 'تلفن']);
        $roleSlug = $this->getValue($row, ['role', 'نقش']);
        $status = $this->getValue($row, ['status', 'is_active', 'وضعیت']) ?: 'فعال';
        $avatar = $this->getValue($row, ['avatar']);
        $socialProvider = $this->getValue($row, ['social_provider']);
        $socialId = $this->getValue($row, ['social_id']);
        $lastLoginIp = $this->getValue($row, ['last_login_ip']);
        $twoFactorEnabled = $this->getValue($row, ['two_factor_enabled']);

        // تبدیل وضعیت
        $isActive = in_array($status, ['فعال', 'active', '1', 'true', 'بله'], true);

        // بررسی وجود کاربر
        $user = User::where('email', $email)->first();

        if ($user) {
            // به‌روزرسانی کاربر موجود
            $updateData = [];

            if ($name) $updateData['name'] = $name;
            if ($phone) $updateData['phone'] = $phone;
            if ($avatar) $updateData['avatar'] = $avatar;
            if ($socialProvider) $updateData['social_provider'] = $socialProvider;
            if ($socialId) $updateData['social_id'] = $socialId;
            if ($lastLoginIp) $updateData['last_login_ip'] = $lastLoginIp;

            $updateData['is_active'] = $isActive;
            $updateData['two_factor_enabled'] = in_array($twoFactorEnabled, ['بله', '1', 'true', 'yes'], true);

            // تاریخ‌های تایید
            $emailVerified = $this->getValue($row, ['email_verified_at']);
            if ($emailVerified) {
                $updateData['email_verified_at'] = $this->parseDate($emailVerified);
            }

            $phoneVerified = $this->getValue($row, ['phone_verified_at']);
            if ($phoneVerified) {
                $updateData['phone_verified_at'] = $this->parseDate($phoneVerified);
            }

            $user->update($updateData);
            $this->updated++;
        } else {
            // ایجاد کاربر جدید
            try {
                $user = User::create([
                    'name' => $name ?: 'کاربر جدید',
                    'email' => $email,
                    'phone' => $phone,
                    'password' => Hash::make('password'),
                    'is_active' => $isActive,
                    'avatar' => $avatar,
                    'social_provider' => $socialProvider,
                    'social_id' => $socialId,
                    'last_login_ip' => $lastLoginIp,
                    'two_factor_enabled' => in_array($twoFactorEnabled, ['بله', '1', 'true', 'yes'], true),
                    'email_verified_at' => $this->parseDate($this->getValue($row, ['email_verified_at'])) ?: now(),
                    'phone_verified_at' => $this->parseDate($this->getValue($row, ['phone_verified_at'])),
                ]);
                $this->created++;
            } catch (\Exception $e) {
                $this->skipped++;
                $this->errors[] = "ایمیل «{$email}»: خطا در ذخیره - {$e->getMessage()}";
                return;
            }
        }

        // اختصاص نقش
        if ($roleSlug && $user) {
            $role = Role::where('slug', $roleSlug)->first();
            if ($role) {
                $user->roles()->syncWithoutDetaching([$role->id]);
            }
        }
    }

    /**
     * خواندن مقدار از ردیف با پشتیبانی از چند نام ستون
     *
     * @param  mixed  $row
     * @param  array  $keys
     * @return mixed
     */
    protected function getValue($row, array $keys)
    {
        foreach ($keys as $key) {
            if (isset($row[$key]) && $row[$key] !== null && $row[$key] !== '') {
                return $row[$key];
            }
        }
        return null;
    }

    /**
     * تبدیل رشته تاریخ به نمونه Carbon
     *
     * @param  mixed  $value
     * @return \Carbon\Carbon|null
     */
    protected function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }
}