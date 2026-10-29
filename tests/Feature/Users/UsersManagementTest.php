<?php

namespace Tests\Feature\Users;

use App\Models\Admin;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\Role;
use Tests\TestCase;

/**
 * تب‌های بخش کاربران: کاربران / ادمین‌ها / نقش‌ها
 *
 * قوانین: کاربران هرگز ارتقا سطح پیدا نمی‌کنند؛ ادمین‌ها فقط از داخل پنل
 * و با گارد admin ساخته/مدیریت می‌شوند.
 */
class UsersManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    protected function makeUser(string $email, string $roleSlug): User
    {
        $user = User::create([
            'name' => $roleSlug,
            'email' => $email,
            'password' => 'password',
            'is_active' => true,
        ]);
        $user->roles()->attach(Role::where('slug', $roleSlug)->firstOrFail());

        return $user;
    }

    // ===== دسترسی =====

    public function test_guest_is_redirected_from_users(): void
    {
        $this->get('/admin/users')->assertRedirect('/admin/login');
        $this->get('/admin/users/roles')->assertRedirect('/admin/login');
        $this->get('/admin/admins')->assertRedirect('/admin/login');
    }

    public function test_admin_can_view_users_list(): void
    {
        $this->actingAsAdmin($this->makeAdmin('super@test.com', 'super-admin'));

        $this->get('/admin/users')
            ->assertOk()
            ->assertViewIs('users::index');
    }

    public function test_admin_can_view_roles_page(): void
    {
        $this->actingAsAdmin($this->makeAdmin('super@test.com', 'super-admin'));

        $this->get('/admin/users/roles')->assertOk();
    }

    public function test_plain_user_cannot_access_users(): void
    {
        // قانون پروژه: دسترسی غیرمجاز = فقط صفحه «یافت نشد» (404)
        $this->actingAs($this->makeUser('plain@test.com', 'user'));

        $this->get('/admin/users')->assertNotFound();
        $this->get('/admin/users/roles')->assertNotFound();
        $this->get('/admin/admins')->assertNotFound();
    }

    // ===== تب کاربران =====

    public function test_admin_can_view_user_profile(): void
    {
        $this->actingAsAdmin($this->makeAdmin('super@test.com', 'super-admin'));
        $target = $this->makeUser('target@test.com', 'user');

        $this->get('/admin/users/'.$target->id)
            ->assertOk()
            ->assertViewIs('users::show');
    }

    public function test_admin_can_toggle_user_status(): void
    {
        $this->actingAsAdmin($this->makeAdmin('super@test.com', 'super-admin'));
        $target = $this->makeUser('target@test.com', 'user');
        $this->assertTrue($target->is_active);

        $this->patch('/admin/users/'.$target->id.'/toggle-status')
            ->assertRedirect();

        $this->assertFalse($target->fresh()->is_active);

        $this->patch('/admin/users/'.$target->id.'/toggle-status');
        $this->assertTrue($target->fresh()->is_active);
    }

    // ===== جلوگیری از ارتقا سطح کاربران =====

    public function test_user_role_change_route_no_longer_exists(): void
    {
        // مسیر قدیمی تغییر نقش کاربر حذف شده — هیچ راهی برای ارتقای کاربر نیست
        $this->actingAsAdmin($this->makeAdmin('super@test.com', 'super-admin'));
        $target = $this->makeUser('target@test.com', 'user');

        $this->from('/admin/users/'.$target->id)
            ->put('/admin/users/'.$target->id.'/role', ['role' => 'super-admin'])
            ->assertNotFound();

        $this->assertEquals('user', $target->fresh()->roles->first()->slug);
    }

    // ===== تب ادمین‌ها =====

    public function test_admin_can_view_admins_tab(): void
    {
        $this->actingAsAdmin($this->makeAdmin('super@test.com', 'super-admin'));

        $this->get('/admin/admins')
            ->assertOk()
            ->assertViewIs('users::admins');
    }

    public function test_admin_can_create_staff_admin(): void
    {
        $this->actingAsAdmin($this->makeAdmin('super@test.com', 'super-admin'));

        $this->post('/admin/admins', [
            'name' => 'مشاور جدید',
            'email' => 'new-staff@test.com',
            'phone' => '09127778899',
            'password' => 'secret-pass-123',
            'password_confirmation' => 'secret-pass-123',
            'role' => 'consultant',
        ])->assertRedirect(route('admin.admins.index'));

        $admin = Admin::where('email', 'new-staff@test.com')->firstOrFail();
        $this->assertTrue($admin->hasRole('consultant'));
        // ساخته‌شده در جدول admins — نه users
        $this->assertDatabaseMissing('users', ['email' => 'new-staff@test.com']);
    }

    public function test_staff_admin_cannot_be_created_with_user_role(): void
    {
        $this->actingAsAdmin($this->makeAdmin('super@test.com', 'super-admin'));

        $this->post('/admin/admins', [
            'name' => 'تقلب',
            'email' => 'cheat@test.com',
            'phone' => '09127778800',
            'password' => 'secret-pass-123',
            'password_confirmation' => 'secret-pass-123',
            'role' => 'user',
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('admins', ['email' => 'cheat@test.com']);
    }

    public function test_admin_can_change_staff_role(): void
    {
        $super = $this->makeAdmin('super@test.com', 'super-admin');
        $this->actingAsAdmin($super);

        $staff = $this->makeAdmin('editor-staff@test.com', 'editor');

        $this->put('/admin/admins/'.$staff->id.'/role', ['role' => 'support'])
            ->assertRedirect();

        $staff->refresh()->load('roles');
        $this->assertEquals('support', $staff->roles->first()->slug);
    }

    public function test_staff_role_change_rejects_user_role(): void
    {
        $super = $this->makeAdmin('super@test.com', 'super-admin');
        $this->actingAsAdmin($super);

        $staff = $this->makeAdmin('editor-staff@test.com', 'editor');

        $this->from('/admin/admins')
            ->put('/admin/admins/'.$staff->id.'/role', ['role' => 'user'])
            ->assertSessionHasErrors('role');
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $super = $this->makeAdmin('super@test.com', 'super-admin');
        $this->actingAsAdmin($super);

        $this->delete('/admin/admins/'.$super->id)->assertRedirect();

        $this->assertNotNull($super->fresh());
    }
}
