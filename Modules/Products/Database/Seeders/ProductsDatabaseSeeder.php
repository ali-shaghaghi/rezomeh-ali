<?php

namespace Modules\Products\Database\Seeders;

use Illuminate\Database\Seeder;

class ProductsDatabaseSeeder extends Seeder
{
    /**
     * سیدر اصلی ماژول محصولات — همه سیدرهای این ماژول را فراخوانی می‌کند
     */
    public function run(): void
    {
        $this->call([
            ProductsPermissionSeeder::class,
            ProductsContentSeeder::class,
        ]);
    }
}
