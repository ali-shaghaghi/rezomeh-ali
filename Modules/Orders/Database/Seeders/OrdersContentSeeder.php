<?php

namespace Modules\Orders\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\OrderTask;
use Modules\Orders\Models\Payment;

class OrdersContentSeeder extends Seeder
{
    /**
     * محتوای نمونه سفارشات (سفارش‌ها + پرداخت‌ها + تسک‌های برنامه کاری)
     */
    public function run(): void
    {
        $admin = User::first();
        if (! $admin) {
            return;
        }

        // ===== سفارش‌های نمونه (idempotent بر اساس order_number) =====
        $orderDefs = [
            ['ORD-1001', 'طراحی سایت فروشگاهی', 'سفارش طراحی فروشگاه اینترنتی با لاراول', 'completed', 'high', 45000000, 45000000, 2, -60, -20],
            ['ORD-1002', 'اپلیکیشن مدیریت رستوران', 'سامانه مدیریت سفارش رستوران با پنل ادمین', 'development', 'urgent', 80000000, 40000000, 1, -10, 20],
            ['ORD-1003', 'سایت شخصی رزومه‌ای', 'طراحی سایت رزومه با وبلاگ و پنل کاربری', 'accepted', 'medium', 25000000, 10000000, 1, -5, 35],
            ['ORD-1004', 'ربات تلگرام اطلاع‌رسانی', 'ربات ارسال خودکار محتوا در تلگرام', 'review', 'low', 12000000, 0, 0, 0, 15],
            ['ORD-1005', 'بهینه‌سازی SEO سایت', 'بهبود سرعت و سئوی سایت موجود', 'pending', 'medium', 8000000, 0, 0, 3, 25],
            ['ORD-1006', 'پنل مدیریت انبار', 'سامانه مدیریت موجودی با اکسل و گزارش', 'cancelled', 'low', 30000000, 5000000, 1, -30, 10],
        ];

        foreach ($orderDefs as [$number, $title, $description, $status, $priority, $amount, $paidTarget, $paymentCount, $startOffset, $deadlineOffset]) {
            $order = Order::firstOrCreate(['order_number' => $number], [
                'user_id' => $admin->id,
                'title' => $title,
                'description' => $description,
                'status' => $status,
                'amount' => $amount,
                'priority' => $priority,
                'start_date' => $startOffset !== 0 ? now()->addDays($startOffset) : null,
                'deadline' => now()->addDays($deadlineOffset),
            ]);

            // پرداخت‌ها فقط برای سفارش تازه‌ساخته‌شده
            if (! $order->wasRecentlyCreated || $paymentCount === 0) {
                continue;
            }

            $perPayment = $paidTarget / $paymentCount;
            for ($i = 1; $i <= $paymentCount; $i++) {
                Payment::create([
                    'user_id' => $admin->id,
                    'order_id' => $order->id,
                    'amount' => $perPayment,
                    'status' => 'completed',
                    'payment_method' => ['زرین‌پال', 'کارت به کارت', 'آی‌دی‌پی'][rand(0, 2)],
                    'transaction_id' => 'TX-'.strtoupper(Str::random(8)),
                    'paid_at' => now()->subDays(rand(1, 30)),
                ]);
            }
            $order->update(['paid_amount' => $paidTarget]);
        }

        // ===== تسک‌های برنامه کاری (نمونه — فقط سفارش تازه) =====
        $taskDefs = [
            'ORD-1002' => [
                ['طراحی دیتابیس و معماری', 'طراحی ERD و مایگریشن‌های اولیه', 'done', 100, 1, 5],
                ['پیاده‌سازی پنل ادمین', 'CRUD محصولات و سفارش‌ها', 'in_progress', 55, 6, 15],
                ['اپ موبایل کاربر (PWA)', 'ثبت سفارش آنلاین مشتری', 'todo', 0, 16, 25],
                ['آزمون و تحویل نهایی', 'تست‌های پذیرش و دیپلوی', 'todo', 0, 26, 30],
            ],
            'ORD-1003' => [
                ['طراحی رابط کاربری', 'پیاده‌سازی UI با تیلویند', 'done', 100, 1, 6],
                ['پیاده‌سازی وبلاگ', 'ماژول مقالات و نظرات', 'in_progress', 40, 7, 14],
                ['اتصال درگاه پرداخت', 'زرین‌پال + آی‌دی‌پی', 'todo', 0, 15, 20],
            ],
            'ORD-1005' => [
                ['تحلیل سئوی فعلی', 'گزارش کامل نقاط ضعف', 'todo', 0, 3, 10],
                ['بهینه‌سازی سرعت', 'فشرده‌سازی و کش', 'todo', 0, 11, 18],
                ['تولید محتوای سئو', '۱۰ مقاله بهینه', 'todo', 0, 19, 25],
            ],
        ];

        foreach ($taskDefs as $number => $tasks) {
            $order = Order::where('order_number', $number)->first();
            if (! $order || $order->tasks()->exists()) {
                continue;
            }

            foreach ($tasks as $i => [$tTitle, $tDesc, $tStatus, $tProgress, $tStart, $tDue]) {
                OrderTask::create([
                    'order_id' => $order->id,
                    'title' => $tTitle,
                    'description' => $tDesc,
                    'status' => $tStatus,
                    'progress' => $tProgress,
                    'start_date' => now()->addDays($tStart),
                    'due_date' => now()->addDays($tDue),
                    'sort_order' => $i + 1,
                ]);
            }
        }
    }
}
