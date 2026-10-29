<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Core\Models\BlogPost;
use Modules\Core\Models\Category;
use Modules\Core\Models\Tag;

class DashboardSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        if (! $admin) {
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

        // Create Blog Posts (idempotent — اگر قبلاً ساخته شده باشد رد می‌شود)
        $postDefs = [
            [
                'user_id' => $admin->id,
                'title' => 'معرفی Laravel 13',
                'slug' => 'laravel-13-intro',
                'excerpt' => 'مروری بر ویژگی‌های جدید Laravel 13',
                'content' => 'محتوای کامل مقاله در اینجا قرار می‌گیرد...',
                'status' => 'published',
                'views' => 245,
                'published_at' => now()->subDays(3),
            ],
            [
                'user_id' => $admin->id,
                'title' => 'آموزش Livewire 3',
                'slug' => 'livewire-3-tutorial',
                'excerpt' => 'آموزش کامل Livewire 3 برای مبتدیان',
                'content' => 'محتوای کامل مقاله در اینجا قرار می‌گیرد...',
                'status' => 'published',
                'views' => 189,
                'published_at' => now()->subDays(7),
            ],
            [
                'user_id' => $admin->id,
                'title' => 'بهینه‌سازی عملکرد وب',
                'slug' => 'web-performance',
                'excerpt' => 'نکاتی برای بهبود سرعت وب‌سایت',
                'content' => 'محتوای کامل مقاله در اینجا قرار می‌گیرد...',
                'status' => 'published',
                'views' => 156,
                'published_at' => now()->subDays(14),
            ],
        ];

        $posts = [];
        foreach ($postDefs as $def) {
            $posts[] = BlogPost::firstOrCreate(['slug' => $def['slug']], $def);
        }

        // Attach categories and tags (فقط برای مقالات تازه‌ساخته‌شده)
        foreach ($posts as $post) {
            if ($post->wasRecentlyCreated) {
                $post->categories()->attach($categories[array_rand($categories)]);
                $post->tags()->attach(array_slice($tags, 0, 2));
            }
        }

        // سفارشات و پرداخت‌ها → ماژول Orders (OrdersContentSeeder)
        // تیکت‌ها → ماژول Tickets (TicketsContentSeeder)
    }
}
