<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Modules\Core\Models\Order;
use Modules\Core\Models\Payment;
use Modules\Core\Models\Ticket;
use Modules\Core\Models\BlogPost;
use Modules\Core\Models\Category;
use Modules\Core\Models\Tag;

class DashboardSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        if (!$admin) {
            return; // No users found
        }

        // Create Categories
        $categories = [
            Category::firstOrCreate(['slug' => 'web-design'], ['name' => 'طراحی وب', 'description' => 'مقالات مربوط به طراحی وب']),
            Category::firstOrCreate(['slug' => 'frontend'], ['name' => 'توسعه فرانت‌اند', 'description' => 'مقالات مربوط به توسعه فرانت‌اند']),
            Category::firstOrCreate(['slug' => 'ux'], ['name' => 'تجربه کاربری', 'description' => 'مقالات مربوط به تجربه کاربری']),
        ];

        // Create Tags
        $tags = [
            Tag::firstOrCreate(['slug' => 'react'], ['name' => 'React']),
            Tag::firstOrCreate(['slug' => 'vuejs'], ['name' => 'Vue.js']),
            Tag::firstOrCreate(['slug' => 'tailwind'], ['name' => 'Tailwind CSS']),
            Tag::firstOrCreate(['slug' => 'javascript'], ['name' => 'JavaScript']),
            Tag::firstOrCreate(['slug' => 'typescript'], ['name' => 'TypeScript']),
        ];

        // Create Blog Posts
        $posts = [
            BlogPost::create([
                'user_id' => $admin->id,
                'title' => 'معرفی Laravel 13',
                'slug' => 'laravel-13-intro',
                'excerpt' => 'مروری بر ویژگی‌های جدید Laravel 13',
                'content' => 'محتوای کامل مقاله در اینجا قرار می‌گیرد...',
                'status' => 'published',
                'views' => 245,
                'published_at' => now()->subDays(3),
            ]),
            BlogPost::create([
                'user_id' => $admin->id,
                'title' => 'آموزش Livewire 3',
                'slug' => 'livewire-3-tutorial',
                'excerpt' => 'آموزش کامل Livewire 3 برای مبتدیان',
                'content' => 'محتوای کامل مقاله در اینجا قرار می‌گیرد...',
                'status' => 'published',
                'views' => 189,
                'published_at' => now()->subDays(7),
            ]),
            BlogPost::create([
                'user_id' => $admin->id,
                'title' => 'بهینه‌سازی عملکرد وب',
                'slug' => 'web-performance',
                'excerpt' => 'نکاتی برای بهبود سرعت وب‌سایت',
                'content' => 'محتوای کامل مقاله در اینجا قرار می‌گیرد...',
                'status' => 'published',
                'views' => 156,
                'published_at' => now()->subDays(14),
            ]),
        ];

        // Attach categories and tags
        foreach ($posts as $post) {
            $post->categories()->attach($categories[array_rand($categories)]);
            $post->tags()->attach(array_slice($tags, 0, 2));
        }

        // Create Orders
        $statuses = ['pending', 'review', 'accepted', 'development', 'completed'];
        for ($i = 1; $i <= 15; $i++) {
            Order::create([
                'user_id' => $admin->id,
                'order_number' => 'ORD-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'title' => 'پروژه ' . $i,
                'description' => 'توضیحات سفارش ' . $i,
                'status' => $statuses[array_rand($statuses)],
                'amount' => rand(5000000, 50000000),
                'paid_amount' => rand(0, 50000000),
                'priority' => ['low', 'medium', 'high'][rand(0, 2)],
            ]);
        }

        // Create Payments
        for ($i = 1; $i <= 10; $i++) {
            Payment::create([
                'user_id' => $admin->id,
                'order_id' => rand(1, 15),
                'amount' => rand(1000000, 20000000),
                'status' => ['completed', 'pending', 'failed'][rand(0, 2)],
                'payment_method' => ['زرین‌پال', 'کارت به کارت', 'آی‌دی‌پی'][rand(0, 2)],
                'paid_at' => now()->subDays(rand(0, 30)),
            ]);
        }

        // Create Tickets
        $ticketStatuses = ['open', 'in_progress', 'waiting', 'closed'];
        $priorities = ['low', 'medium', 'high', 'urgent'];
        for ($i = 1; $i <= 8; $i++) {
            Ticket::create([
                'user_id' => $admin->id,
                'subject' => 'تیکت پشتیبانی ' . $i,
                'message' => 'متن تیکت ' . $i,
                'status' => $ticketStatuses[array_rand($ticketStatuses)],
                'priority' => $priorities[array_rand($priorities)],
                'category' => ['فنی', 'مالی', 'عمومی'][rand(0, 2)],
            ]);
        }
    }
}