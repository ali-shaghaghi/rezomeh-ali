<?php

if (!function_exists('send_sms')) {
    /**
     * ارسال پیامک با قالب — مثال: send_sms('09121234567', 'consultation_booked', ['name' => 'علی'])
     *
     * @param  array<string, string>  $vars
     */
    function send_sms(string $phone, string $template, array $vars = [], ?\App\Models\User $user = null): \Modules\SMS\Models\SmsLog
    {
        return app(\Modules\SMS\Services\SmsService::class)->send($phone, $template, $vars, $user);
    }
}

if (!function_exists('send_sms_raw')) {
    /**
     * ارسال پیامک با متن خام (بدون قالب)
     */
    function send_sms_raw(string $phone, string $message, ?\App\Models\User $user = null): \Modules\SMS\Models\SmsLog
    {
        return app(\Modules\SMS\Services\SmsService::class)->sendRaw($phone, $message, $user);
    }
}