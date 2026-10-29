<?php

namespace Modules\Resume\Database\Seeders;

use Illuminate\Database\Seeder;

class ResumeDatabaseSeeder extends Seeder
{
    /**
     * سیدر اصلی ماژول رزومه — همه سیدرهای این ماژول را فراخوانی می‌کند
     */
    public function run(): void
    {
        $this->call([
            ResumePermissionSeeder::class,
            ResumeContentSeeder::class,
        ]);
    }
}
