<?php

namespace Tests\Feature\Tickets;

use App\Models\Admin;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\Role;
use Modules\Tickets\Database\Seeders\TicketsPermissionSeeder;
use Modules\Tickets\Models\Ticket;
use Modules\Tickets\Models\TicketReply;
use Modules\Tickets\Services\TicketService;
use Tests\TestCase;

class TicketsAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $superAdmin;

    protected User $author;

    protected User $support;

    protected User $plainUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(TicketsPermissionSeeder::class);

        $superRole = Role::where('slug', 'super-admin')->firstOrFail();
        $supportRole = Role::where('slug', 'support')->firstOrFail();
        $userRole = Role::where('slug', 'user')->firstOrFail();

        $this->superAdmin = $this->makeAdmin('super@test.com', 'super-admin');

        // کاربر عادی برای FK جدول users (تیکت‌ها)
        $this->author = User::create(['name' => 'Author', 'email' => 'tickets-author@test.com', 'password' => 'password', 'is_active' => true]);
        $this->author->roles()->attach($userRole);

        $this->support = User::create(['name' => 'Support', 'email' => 'support@test.com', 'password' => 'password', 'is_active' => true]);
        $this->support->roles()->attach($supportRole);

        $this->plainUser = User::create(['name' => 'User', 'email' => 'user@test.com', 'password' => 'password', 'is_active' => true]);
        $this->plainUser->roles()->attach($userRole);
    }

    // ===== تست Policies =====

    public function test_super_admin_can_manage_tickets(): void
    {
        $ticket = Ticket::create(['user_id' => $this->plainUser->id, 'subject' => 'تست', 'message' => 'متن']);

        $this->assertTrue($this->superAdmin->can('viewAny', Ticket::class));
        $this->assertTrue($this->superAdmin->can('update', $ticket));
        $this->assertTrue($this->superAdmin->can('delete', $ticket));
    }

    public function test_support_can_manage_tickets(): void
    {
        $ticket = Ticket::create(['user_id' => $this->plainUser->id, 'subject' => 'تست', 'message' => 'متن']);

        $this->assertTrue($this->support->can('viewAny', Ticket::class));
        $this->assertTrue($this->support->can('update', $ticket));
    }

    public function test_plain_user_cannot_manage_tickets(): void
    {
        $ticket = Ticket::create(['user_id' => $this->author->id, 'subject' => 'تست', 'message' => 'متن']);

        $this->assertFalse($this->plainUser->can('viewAny', Ticket::class));
        $this->assertFalse($this->plainUser->can('update', $ticket));
        $this->assertFalse($this->plainUser->can('delete', $ticket));
    }

    // ===== تست دسترسی مسیرها =====

    public function test_guest_is_redirected_from_tickets(): void
    {
        $this->get('/admin/tickets')->assertRedirect('/admin/login');
    }

    public function test_admin_can_access_tickets_pages(): void
    {
        $this->actingAs($this->superAdmin, 'admin');

        $ticket = Ticket::create(['user_id' => $this->author->id, 'subject' => 'تست روت', 'message' => 'متن']);

        $this->get('/admin/tickets')->assertOk();
        $this->get('/admin/tickets/open')->assertOk();
        $this->get('/admin/tickets/'.$ticket->id)->assertOk();
    }

    public function test_plain_user_is_forbidden_from_tickets(): void
    {
        $this->actingAs($this->plainUser);

        // قانون پروژه: دسترسی غیرمجاز = فقط صفحه «یافت نشد» (404)
        $this->get('/admin/tickets')->assertNotFound();
        $this->get('/admin/tickets/open')->assertNotFound();
    }

    // ===== تست Service =====

    public function test_ticket_service_search_and_filter(): void
    {
        $service = app(TicketService::class);

        Ticket::create(['user_id' => $this->author->id, 'subject' => 'مشکل پرداخت', 'message' => 'متن', 'status' => 'open']);
        Ticket::create(['user_id' => $this->author->id, 'subject' => 'سوال تحویل', 'message' => 'متن', 'status' => 'closed']);

        $this->assertEquals(1, $service->list(['search' => 'پرداخت'])->total());
        $this->assertEquals(1, $service->list(['status' => 'open'])->total());
        $this->assertEquals(1, $service->list(['status' => 'closed'])->total());
    }

    public function test_ticket_service_add_reply_updates_status(): void
    {
        $service = app(TicketService::class);
        $ticket = Ticket::create(['user_id' => $this->plainUser->id, 'subject' => 'گفتگو', 'message' => 'متن', 'status' => 'open']);

        // پاسخ کارشناس → در انتظار پاسخ کاربر
        $service->addReply($ticket, $this->author->id, 'پاسخ کارشناس', true);
        $this->assertEquals('waiting', $ticket->fresh()->status);
        $this->assertTrue($ticket->fresh()->replies->first()->is_staff_reply);

        // پاسخ کاربر → در حال بررسی
        $service->addReply($ticket, $this->plainUser->id, 'پاسخ کاربر', false);
        $this->assertEquals('in_progress', $ticket->fresh()->status);
        $this->assertEquals(2, $ticket->fresh()->replies->count());
    }

    public function test_ticket_service_set_status_and_priority(): void
    {
        $service = app(TicketService::class);
        $ticket = Ticket::create(['user_id' => $this->author->id, 'subject' => 'تست', 'message' => 'متن', 'priority' => 'low']);

        $service->setStatus($ticket, 'closed');
        $this->assertEquals('closed', $ticket->fresh()->status);

        $service->setPriority($ticket, 'urgent');
        $this->assertEquals('urgent', $ticket->fresh()->priority);
    }

    public function test_ticket_service_stats(): void
    {
        $service = app(TicketService::class);

        Ticket::create(['user_id' => $this->author->id, 'subject' => '۱', 'message' => 'م', 'status' => 'open']);
        Ticket::create(['user_id' => $this->author->id, 'subject' => '۲', 'message' => 'م', 'status' => 'closed']);

        $stats = $service->stats();

        $this->assertEquals(2, $stats['total']);
        $this->assertEquals(1, $stats['open']);
        $this->assertEquals(1, $stats['closed']);
    }

    public function test_ticket_service_delete_removes_replies(): void
    {
        $service = app(TicketService::class);
        $ticket = Ticket::create(['user_id' => $this->author->id, 'subject' => 'حذف', 'message' => 'متن']);

        TicketReply::create(['ticket_id' => $ticket->id, 'user_id' => $this->author->id, 'message' => 'پاسخ', 'is_staff_reply' => true]);

        $service->delete($ticket);

        $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]);
        $this->assertEquals(0, TicketReply::where('ticket_id', $ticket->id)->count());
    }
}
