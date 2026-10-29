<?php

namespace Modules\Tickets\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Tickets\Models\Ticket;
use Modules\Tickets\Models\TicketReply;

class TicketsContentSeeder extends Seeder
{
    /**
     * محتوای نمونه تیکت‌ها (تیکت‌ها + پاسخ‌ها)
     */
    public function run(): void
    {
        $admin = User::first();
        if (! $admin) {
            return;
        }

        // ===== تیکت‌های نمونه (idempotent بر اساس subject) =====
        $ticketDefs = [
            ['خطا در فرآیند پرداخت', 'سلام، هنگام پرداخت سفارش ORD-1003 با خطای درگاه مواجه می‌شوم. لطفاً بررسی کنید.', 'open', 'urgent', 'فنی', [
                ['سلام وقت بخیر، بررسی کردیم؛ مشکل از تنظیمات درگاه بود که اصلاح شد. لطفاً دوباره تلاش کنید.', true],
                ['ممنون، مشکل حل شد!', false],
            ]],
            ['سوال درباره زمان تحویل پروژه', 'سلام، سفارش طراحی سایت فروشگاهی من حدوداً چه زمانی تحویل داده می‌شود؟', 'in_progress', 'medium', 'عمومی', [
                ['سلام، طبق برنامه‌ریزی پروژه شما تا پایان هفته آینده تحویل داده می‌شود.', true],
            ]],
            ['درخواست فاکتور رسمی', 'سلام، برای سفارش ORD-1001 به فاکتور رسمی نیاز دارم. لطفاً راهنمایی کنید.', 'waiting', 'low', 'مالی', [
                ['سلام، فاکتور رسمی پس از تسویه کامل صادر می‌شود. اطلاعات شرکت را ارسال کنید تا آغاز کنیم.', true],
            ]],
            ['مشکل در ورود به پنل کاربری', 'رمز عبورم را فراموش کرده‌ام و ایمیل بازیابی دریافت نمی‌کنم.', 'closed', 'medium', 'فنی', [
                ['سلام، ایمیل شما در لیست خبرنامه نبود؛ اصلاح شد. لطفاً دوباره «فراموشی رمز» را امتحان کنید.', true],
                ['دریافت کردم، ممنون از پیگیری سریع شما.', false],
                ['خوشحالیم مشکل حل شد. تیکت بسته می‌شود.', true],
            ]],
            ['پیشنهاد قابلیت جدید', 'سلام، پیشنهاد می‌کنم امکان دانلود خروجی اکسل سفارشات هم به پنل کاربری اضافه شود.', 'open', 'low', 'عمومی', []],
        ];

        foreach ($ticketDefs as [$subject, $message, $status, $priority, $category, $replies]) {
            $ticket = Ticket::firstOrCreate(['subject' => $subject], [
                'user_id' => $admin->id,
                'subject' => $subject,
                'message' => $message,
                'status' => $status,
                'priority' => $priority,
                'category' => $category,
            ]);

            // پاسخ‌ها فقط برای تیکت تازه‌ساخته‌شده
            if (! $ticket->wasRecentlyCreated) {
                continue;
            }

            foreach ($replies as [$replyMessage, $isStaff]) {
                TicketReply::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $admin->id,
                    'message' => $replyMessage,
                    'is_staff_reply' => $isStaff,
                ]);
            }
        }
    }
}
