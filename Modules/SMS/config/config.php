<?php

return [

    /*
    |--------------------------------------------------------------------------
    | تنظیمات ماژول پیامک
    |--------------------------------------------------------------------------
    */

    // درایور پیش‌فرض وقتی هیچ ارائه‌دهنده‌ای در دیتابیس ثبت نشده
    'default_driver' => env('SMS_DRIVER', 'log'),

    // کلیدهای API پیش‌فرض (اولویت با تنظیمات پنل ادمین است)
    'kavenegar_api_key' => env('KAVENEGAR_API_KEY', ''),
    'kavenegar_sender' => env('KAVENEGAR_SENDER', ''),
    'ghasedak_api_key' => env('GHASEDAK_API_KEY', ''),
    'ghasedak_sender' => env('GHASEDAK_SENDER', ''),
    'melipayamak_username' => env('MELIPAYAMAK_USERNAME', ''),
    'melipayamak_password' => env('MELIPAYAMAK_PASSWORD', ''),
    'melipayamak_sender' => env('MELIPAYAMAK_SENDER', ''),

    // حداقل فاصله بین دو پیامک به یک شماره (ثانیه) — جلوگیری از اسپم
    'rate_limit_seconds' => 10,
];