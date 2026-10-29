<?php

namespace Modules\SMS\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\SMS\Models\SmsTemplate;

class SmsTemplateSeeder extends Seeder
{
    /**
     * قالب‌های پیش‌فرض پیامک — قابل ویرایش از پنل ادمین
     */
    public function run(): void
    {
        $templates = [
            [
                'key' => 'consultation_booked',
                'title' => 'ثبت مشاوره',
                'body' => '{name} عزیز، درخواست مشاوره شما برای {date} ساعت {time} ثبت شد. علی شقاقی',
                'variables' => ['name', 'date', 'time'],
            ],
            [
                'key' => 'consultation_booked_user',
                'title' => 'رزرو مشاوره (کاربر)',
                'body' => 'سلام {name}، مشاوره شما با {consultant} در تاریخ {date} ساعت {time} ثبت شد. علی شقاقی',
                'variables' => ['name', 'consultant', 'date', 'time'],
            ],
            [
                'key' => 'consultation_booked_admin',
                'title' => 'رزرو مشاوره (مدیر)',
                'body' => 'رزرو جدید: {user} - {consultant} - {date} {time}',
                'variables' => ['user', 'consultant', 'date', 'time'],
            ],
            [
                'key' => 'order_status_changed',
                'title' => 'تغییر وضعیت سفارش',
                'body' => 'سفارش {order_number} شما به «{status}» تغییر کرد. علی شقاقی',
                'variables' => ['order_number', 'status'],
            ],
            [
                'key' => 'ticket_replied',
                'title' => 'پاسخ تیکت',
                'body' => '{name} عزیز، به تیکت «{ticket_subject}» شما پاسخ داده شد. علی شقاقی',
                'variables' => ['name', 'ticket_subject'],
            ],
            [
                'key' => 'project_request_submitted',
                'title' => 'ثبت درخواست پروژه',
                'body' => '{name} عزیز، درخواست پروژه «{title}» شما ثبت شد. قیمت تخمینی: {price} تومان. به‌زودی بررسی و قیمت‌گذاری نهایی می‌شود. علی شقاقی',
                'variables' => ['name', 'title', 'price'],
            ],
            [
                'key' => 'project_proposal_sent',
                'title' => 'ارسال پیشنهاد پروژه',
                'body' => '{name} عزیز، پیشنهاد پروژه «{title}» آماده است: قیمت نهایی {price} تومان در {days} روز با پرداخت اقساطی ۳۰/۳۰/۴۰. علی شقاقی',
                'variables' => ['name', 'title', 'price', 'days'],
            ],
            [
                'key' => 'project_status_changed',
                'title' => 'تغییر وضعیت پروژه',
                'body' => 'پروژه «{title}» شما به وضعیت «{status}» تغییر کرد. علی شقاقی',
                'variables' => ['title', 'status'],
            ],
            [
                'key' => 'otp_code',
                'title' => 'کد یکبار مصرف',
                'body' => 'کد ورود شما: {code}\nاعتبار: {minutes} دقیقه',
                'variables' => ['code', 'minutes'],
            ],
        ];

        foreach ($templates as $template) {
            SmsTemplate::updateOrCreate(
                ['key' => $template['key']],
                $template + ['is_active' => true]
            );
        }
    }
}