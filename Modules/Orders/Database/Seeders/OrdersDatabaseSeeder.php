<?php

namespace Modules\Orders\Database\Seeders;

use Illuminate\Database\Seeder;

class OrdersDatabaseSeeder extends Seeder
{
    /**
     * سیدر اصلی ماژول سفارشات — همه سیدرهای این ماژول را فراخوانی می‌کند
     */
    public function run(): void
    {
        $this->call([
            OrdersPermissionSeeder::class,
            OrdersContentSeeder::class,
        ]);
    }
}
