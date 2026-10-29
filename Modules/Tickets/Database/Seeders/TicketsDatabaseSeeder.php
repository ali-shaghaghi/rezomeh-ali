<?php

namespace Modules\Tickets\Database\Seeders;

use Illuminate\Database\Seeder;

class TicketsDatabaseSeeder extends Seeder
{
    /**
     * سیدر اصلی ماژول تیکت‌ها — همه سیدرهای این ماژول را فراخوانی می‌کند
     */
    public function run(): void
    {
        $this->call([
            TicketsPermissionSeeder::class,
            TicketsContentSeeder::class,
        ]);
    }
}
