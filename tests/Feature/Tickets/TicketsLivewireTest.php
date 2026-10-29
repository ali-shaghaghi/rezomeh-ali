<?php

namespace Tests\Feature\Tickets;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Core\Models\Role;
use Modules\Tickets\Database\Seeders\TicketsPermissionSeeder;
use Modules\Tickets\Livewire\Admin\TicketShow;
use Modules\Tickets\Livewire\Admin\TicketsIndex;
use Modules\Tickets\Models\Ticket;
use Tests\TestCase;

class TicketsLivewireTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(TicketsPermissionSeeder::class);

        $superRole = Role::where('slug', 'super-admin')->firstOrFail();
        $this->admin = User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => 'password', 'is_active' => true]);
        $this->admin->roles()->attach($superRole);
    }

    // ===== TicketsIndex =====

    public function test_tickets_index_renders_tickets(): void
    {
        Ticket::create(['user_id' => $this->admin->id, 'subject' => 'تیکت تستی', 'message' => 'متن تیکت تست']);

        Livewire::actingAs($this->admin)
            ->test(TicketsIndex::class)
            ->assertSee('تیکت تستی')
            ->assertSee('مدیریت تیکت‌ها');
    }

    public function test_tickets_index_search_filters(): void
    {
        Ticket::create(['user_id' => $this->admin->id, 'subject' => 'مشکل درگاه پرداخت', 'message' => 'م']);
        Ticket::create(['user_id' => $this->admin->id, 'subject' => 'سوال درباره تحویل', 'message' => 'م']);

        Livewire::actingAs($this->admin)
            ->test(TicketsIndex::class)
            ->set('search', 'درگاه')
            ->assertSee('مشکل درگاه پرداخت')
            ->assertDontSee('سوال درباره تحویل');
    }

    public function test_tickets_index_status_filter(): void
    {
        Ticket::create(['user_id' => $this->admin->id, 'subject' => 'تیکت باز فعال', 'message' => 'م', 'status' => 'open']);
        Ticket::create(['user_id' => $this->admin->id, 'subject' => 'تیکت بسته قدیمی', 'message' => 'م', 'status' => 'closed']);

        Livewire::actingAs($this->admin)
            ->test(TicketsIndex::class)
            ->set('status', 'closed')
            ->assertSee('تیکت بسته قدیمی')
            ->assertDontSee('تیکت باز فعال');
    }

    public function test_tickets_index_empty_state(): void
    {
        Livewire::actingAs($this->admin)
            ->test(TicketsIndex::class)
            ->assertSee('هنوز تیکتی ثبت نشده است');
    }

    public function test_tickets_index_quick_status_action(): void
    {
        $ticket = Ticket::create(['user_id' => $this->admin->id, 'subject' => 'تست', 'message' => 'م', 'status' => 'open']);

        Livewire::actingAs($this->admin)
            ->test(TicketsIndex::class)
            ->call('quickStatus', $ticket->id, 'closed');

        $this->assertEquals('closed', $ticket->fresh()->status);
    }

    public function test_tickets_index_quick_priority_action(): void
    {
        $ticket = Ticket::create(['user_id' => $this->admin->id, 'subject' => 'تست', 'message' => 'م', 'priority' => 'low']);

        Livewire::actingAs($this->admin)
            ->test(TicketsIndex::class)
            ->call('quickPriority', $ticket->id, 'urgent');

        $this->assertEquals('urgent', $ticket->fresh()->priority);
    }

    public function test_tickets_index_delete_with_confirmation(): void
    {
        $ticket = Ticket::create(['user_id' => $this->admin->id, 'subject' => 'حذف شونده', 'message' => 'م']);

        $component = Livewire::actingAs($this->admin)
            ->test(TicketsIndex::class)
            ->call('confirmDelete', $ticket->id)
            ->assertSet('confirmingDelete', true)
            ->call('deleteTicket');

        $this->assertFalse($component->get('confirmingDelete'));
        $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]);
    }

    public function test_tickets_index_open_initial_filter(): void
    {
        Ticket::create(['user_id' => $this->admin->id, 'subject' => 'فیلتر تیکت باز', 'message' => 'م', 'status' => 'open']);
        Ticket::create(['user_id' => $this->admin->id, 'subject' => 'فیلتر تیکت بسته', 'message' => 'م', 'status' => 'closed']);

        Livewire::actingAs($this->admin)
            ->test(TicketsIndex::class, ['initialStatus' => 'open'])
            ->assertSee('فیلتر تیکت باز')
            ->assertDontSee('فیلتر تیکت بسته');
    }

    // ===== TicketShow =====

    public function test_ticket_show_renders_conversation(): void
    {
        $ticket = Ticket::create(['user_id' => $this->admin->id, 'subject' => 'گفتگوی تستی', 'message' => 'پیام اصلی تیکت']);
        $ticket->replies()->create(['user_id' => $this->admin->id, 'message' => 'پاسخ نمونه کارشناس', 'is_staff_reply' => true]);

        Livewire::actingAs($this->admin)
            ->test(TicketShow::class, ['ticket' => $ticket])
            ->assertSee('گفتگوی تستی')
            ->assertSee('پیام اصلی تیکت')
            ->assertSee('پاسخ نمونه کارشناس');
    }

    public function test_ticket_show_send_reply(): void
    {
        $ticket = Ticket::create(['user_id' => $this->admin->id, 'subject' => 'پاسخ تست', 'message' => 'م', 'status' => 'open']);

        Livewire::actingAs($this->admin)
            ->test(TicketShow::class, ['ticket' => $ticket])
            ->set('replyMessage', 'این پاسخ تستی کارشناس است')
            ->call('sendReply');

        // پاسخ ثبت شد + وضعیت به waiting تغییر کرد (پاسخ کارشناس)
        $this->assertEquals(1, $ticket->fresh()->replies->count());
        $this->assertEquals('waiting', $ticket->fresh()->status);
        $this->assertTrue($ticket->fresh()->replies->first()->is_staff_reply);
    }

    public function test_ticket_show_reply_validation(): void
    {
        $ticket = Ticket::create(['user_id' => $this->admin->id, 'subject' => 'اعتبارسنجی', 'message' => 'م']);

        Livewire::actingAs($this->admin)
            ->test(TicketShow::class, ['ticket' => $ticket])
            ->set('replyMessage', '')
            ->call('sendReply')
            ->assertHasErrors(['replyMessage' => 'required']);
    }

    public function test_ticket_show_change_status(): void
    {
        $ticket = Ticket::create(['user_id' => $this->admin->id, 'subject' => 'وضعیت', 'message' => 'م', 'status' => 'open']);

        Livewire::actingAs($this->admin)
            ->test(TicketShow::class, ['ticket' => $ticket])
            ->set('newStatus', 'closed')
            ->call('changeStatus');

        $this->assertEquals('closed', $ticket->fresh()->status);
    }
}
