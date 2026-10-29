<?php

namespace Tests\Feature;

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Resume\Database\Seeders\ResumePermissionSeeder;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * صفحه اصلی فرانت‌اند عمومی (فاز ۱۱) باید رندر شود
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(ResumePermissionSeeder::class);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
