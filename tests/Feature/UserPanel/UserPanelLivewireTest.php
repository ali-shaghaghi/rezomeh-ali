<?php

namespace Tests\Feature\UserPanel;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Orders\Models\Order;
use Modules\Settings\Livewire\Admin\ChangePasswordForm;
use Modules\ProjectDev\Livewire\UserPanel\MyProjects;
use Modules\ProjectDev\Services\ProjectService;
use Modules\Tickets\Models\Ticket;
use Modules\UserPanel\Livewire\Dashboard;
use Tests\TestCase;

class UserPanelLivewireTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Order $order;

    private Ticket $ticket;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'ORD-10000001',
            'title' => 'Needle first',
            'description' => 'Owned project description',
            'status' => 'completed',
            'amount' => 1000,
            'paid_amount' => 500,
        ]);

        $this->ticket = Ticket::create([
            'user_id' => $this->user->id,
            'subject' => 'Owned ticket subject',
            'message' => 'Original ticket message',
            'status' => 'open',
            'priority' => 'medium',
            'category' => 'general',
        ]);
    }

    // ------------------------------------------------------------------
    // داشبورد — آمار زنده مالکیت‌محور
    // ------------------------------------------------------------------

    public function test_overview_stats_are_owner_scoped_and_live(): void
    {
        Order::create([
            'user_id' => User::factory()->create()->id,
            'order_number' => 'ORD-10000005',
            'title' => 'Private',
            'description' => 'x',
            'status' => 'completed',
            'amount' => 999,
            'paid_amount' => 999,
        ]);

        Livewire::test(Dashboard::class)
            ->assertViewHas('stats', fn ($stats) => $stats == [
                'orders_total' => 1,
                'orders_active' => 0,
                'orders_completed' => 1,
                'total_spent' => 500.0,
                'tickets_total' => 1,
                'tickets_open' => 1,
            ])
            ->assertViewHas('latestOrders', fn ($orders) => count($orders) === 1)
            ->assertViewHas('latestTickets', fn ($tickets) => count($tickets) === 1);
    }

    // ------------------------------------------------------------------
    // درخواست‌های پروژه — کامپوننت زنده پنل کاربر
    // ------------------------------------------------------------------

    public function test_projects_component_shows_only_owned_requests(): void
    {
        $service = app(ProjectService::class);

        $mine = $service->createRequest($this->user, ['title' => 'پروژه من']);
        $service->createRequest(User::factory()->create(['is_active' => true]), ['title' => 'پروژه دیگران']);

        Livewire::test(MyProjects::class)
            ->assertViewHas('projectStats', fn ($stats) => $stats['projects_total'] === 1)
            ->assertViewHas('projectRequests', fn ($requests) => $requests->total() === 1 && $requests->first()->id === $mine->id)
            ->assertSee('پروژه من')
            ->assertDontSee('پروژه دیگران');
    }

    // ------------------------------------------------------------------
    // تغییر رمز عبور — کامپوننت امنیت پنل کاربر
    // ------------------------------------------------------------------

    public function test_change_password_component_validates_and_updates(): void
    {
        // کامپوننت تغییر رمز — حالا از ماژول Settings (مشترک ادمین/کاربر)
        Livewire::test(ChangePasswordForm::class)
            ->set('currentPassword', 'wrong-password')
            ->set('newPassword', 'new-password-123')
            ->set('newPasswordConfirmation', 'new-password-123')
            ->call('changePassword')
            ->assertSet('errorMessage', 'رمز عبور فعلی صحیح نیست.');

        Livewire::test(ChangePasswordForm::class)
            ->set('currentPassword', 'password')
            ->set('newPassword', 'new-password-123')
            ->set('newPasswordConfirmation', 'new-password-123')
            ->call('changePassword')
            ->assertSet('successMessage', 'رمز عبور با موفقیت تغییر کرد.');

        $this->assertTrue(auth()->attempt(['email' => $this->user->email, 'password' => 'new-password-123']));
    }

    public function test_change_password_component_validates_rules(): void
    {
        Livewire::test(ChangePasswordForm::class)
            ->set('currentPassword', '')
            ->set('newPassword', 'short')
            ->set('newPasswordConfirmation', 'different')
            ->call('changePassword')
            ->assertHasErrors(['currentPassword', 'newPassword', 'newPasswordConfirmation']);
    }
}
