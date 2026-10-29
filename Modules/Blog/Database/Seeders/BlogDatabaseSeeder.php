<?php

namespace Modules\Blog\Database\Seeders;

use Illuminate\Database\Seeder;

class BlogDatabaseSeeder extends Seeder
{
    /**
     * سیدر اصلی ماژول وبلاگ — همه سیدرهای این ماژول را فراخوانی می‌کند
     */
    public function run(): void
    {
        $this->call([
            BlogPermissionSeeder::class,
            BlogContentSeeder::class,
        ]);
    }
}
