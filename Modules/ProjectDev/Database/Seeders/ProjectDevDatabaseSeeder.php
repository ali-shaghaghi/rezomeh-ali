<?php

namespace Modules\ProjectDev\Database\Seeders;

use Illuminate\Database\Seeder;

class ProjectDevDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ProjectDevPermissionSeeder::class,
            ProjectDevContentSeeder::class,
        ]);
    }
}
