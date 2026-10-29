<?php

namespace Tests\Feature\UserPanel;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Orders\Livewire\UserPanel\MyOrders;
use Modules\Orders\Livewire\UserPanel\OrderDetails;
use Modules\Orders\Models\Order;
use Modules\Tickets\Livewire\UserPanel\MyTickets;
use Modules\Tickets\Livewire\UserPanel\TicketConversation;
use Modules\Tickets\Models\Ticket;
use Modules\Tickets\Services\UserTicketService;
use Modules\UserPanel\Livewire\PanelShell;
use Modules\UserPanel\Services\UserPanelService;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * پنل کاربر — معماری شل ریل‌تایم Livewire:
 * سوییچ تب‌ها بدون رفرش و هر بخش از ماژول خودش رندر می‌شود.
 */
class UserPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function beforeRefreshingDatabase(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertEmpty(config('database.connections.sqlite.url'));
    }

    private function order(User $user, array $attributes = []): Order
    {
        return Order::create(array_merge([
            'user_id' => $user->id,
            'order_number' => 'ORD-'.fake()->unique()->numerify('########'),
            'title' => 'Owned project',
            'description' => 'Project description',
            'status' => 'pending',
            'amount' => 1000,
            'paid_amount' => 0,
        ], $attributes));
    }

    private function ticket(User $user, array $attributes = []): Ticket
    {
        return Ticket::create(array_merge([
            'user_id' => $user->id,
            'subject' => 'Owned ticket',
            'message' => 'Original ticket message',
            'status' => 'open',
            'priority' => 'medium',
            'category' => 'general',
        ], $attributes));
    }

    public function test_guests_are_redirected_from_every_panel_endpoint(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user);
        $ticket = $this->ticket($user);

        foreach (['/dashboard', '/dashboard/tickets/new', '/dashboard/tickets/'.$ticket->id, '/dashboard/orders/'.$order->order_number] as $url) {
            $this->get($url)->assertRedirect('/login');
        }

        $this->assertSame('open', $ticket->fresh()->status);
    }

    public function test_shell_renders_with_livewire_tabs(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/dashboard')->assertOk()
            ->assertSeeLivewire('userpanel.panel-shell')
            ->assertSeeLivewire('userpanel.dashboard');
    }

    public function test_shell_switches_tabs_without_page_load(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::withQueryParams(['tab' => 'dashboard'])
            ->test(PanelShell::class)
            ->assertSet('tab', 'dashboard')
            // سوییچ ریل‌تایم به سفارش‌ها — کامپوننت از ماژول Orders رندر می‌شود
            ->call('switchTab', 'orders')
            ->assertSet('tab', 'orders')
            ->assertSeeLivewire('orders.my-orders')
            // سوییچ ریل‌تایم به تیکت‌ها — کامپوننت از ماژول Tickets رندر می‌شود
            ->call('switchTab', 'tickets')
            ->assertSet('tab', 'tickets')
            ->assertSeeLivewire('tickets.my-tickets')
            ->call('switchTab', 'security')
            ->assertSet('tab', 'security')
            ->assertSeeLivewire('auth.account-security');
    }

    public function test_shell_rejects_unknown_tabs(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(PanelShell::class)->call('switchTab', 'hacked')->assertStatus(404);
    }

    public function test_legacy_dashboard_tab_query_opens_matching_tab(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/dashboard?tab=orders')->assertOk()
            ->assertSeeLivewire('orders.my-orders');

        $this->get('/dashboard?tab=tickets')->assertOk()
            ->assertSeeLivewire('tickets.my-tickets');

        $this->get('/dashboard?tab=security')->assertOk()
            ->assertSeeLivewire('auth.account-security');

        // تب نامعتبر → داشبورد پیش‌فرض
        $this->get('/dashboard?tab=unknown')->assertOk()
            ->assertSeeLivewire('userpanel.dashboard');
    }

    public function test_legacy_orders_and_tickets_urls_redirect_301(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $order = $this->order($user);
        $ticket = $this->ticket($user);

        $this->get('/orders')->assertRedirect('/dashboard?tab=orders')->assertStatus(301);
        $this->get('/orders/'.$order->order_number)->assertRedirect('/dashboard/orders/'.$order->order_number);
        $this->get('/tickets')->assertRedirect('/dashboard?tab=tickets')->assertStatus(301);
        $this->get('/tickets/'.$ticket->id)->assertRedirect('/dashboard/tickets/'.$ticket->id);
    }

    public function test_dashboard_statistics_and_recent_records_are_owner_scoped(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->order($user, ['paid_amount' => 100]);
        $this->order($user, ['status' => 'completed', 'paid_amount' => 1000]);
        $this->order($user, ['status' => 'cancelled', 'paid_amount' => 500]);
        $this->ticket($user);
        $this->ticket($user, ['status' => 'waiting']);
        $this->ticket($user, ['status' => 'in_progress']);
        $this->ticket($user, ['status' => 'closed']);
        $this->order($other, ['title' => 'Private other project', 'paid_amount' => 900]);
        $this->ticket($other, ['subject' => 'Private other ticket']);

        $this->actingAs($user)->get('/dashboard')->assertOk()
            ->assertSee('Owned project')->assertSee('Owned ticket')
            ->assertDontSee('Private other project')->assertDontSee('Private other ticket');

        // آمار و آخرین‌ها داخل کامپوننت داشبورد (Livewire) محاسبه می‌شود
        Livewire::test(\Modules\UserPanel\Livewire\Dashboard::class)
            ->assertViewHas('stats', fn ($stats) => $stats == [
                'orders_total' => 3,
                'orders_active' => 1,
                'orders_completed' => 1,
                'total_spent' => 1100,
                'tickets_total' => 4,
                'tickets_open' => 3,
            ])
            ->assertViewHas('latestOrders', fn ($orders) => count($orders) === 3)
            ->assertViewHas('latestTickets', fn ($tickets) => count($tickets) === 4);
    }

    public function test_dashboard_latest_records_ignore_the_page_query(): void
    {
        $user = User::factory()->create();
        for ($i = 0; $i < 6; $i++) {
            $this->order($user, ['title' => 'Project '.$i, 'created_at' => now()->subDays(6 - $i)]);
            $this->ticket($user, ['subject' => 'Ticket '.$i, 'created_at' => now()->subDays(6 - $i)]);
        }

        $this->actingAs($user)->get('/dashboard?page=999')->assertOk()
            ->assertSee('Project 5')->assertSee('Ticket 5');

        Livewire::test(\Modules\UserPanel\Livewire\Dashboard::class)
            ->assertViewHas('latestOrders', fn ($orders) => count($orders) === 5 && $orders[0]->title === 'Project 5')
            ->assertViewHas('latestTickets', fn ($tickets) => count($tickets) === 5 && $tickets[0]->subject === 'Ticket 5');
    }

    // ===== سفارش‌ها (ماژول Orders) =====

    public function test_orders_list_filters_and_paginates_only_owned_records(): void
    {
        $user = User::factory()->create();
        $this->order($user, ['title' => 'Needle first', 'status' => 'completed']);
        $this->order($user, ['title' => 'Needle second', 'status' => 'completed']);
        $this->order($user, ['title' => 'Needle excluded', 'status' => 'pending']);
        $this->order(User::factory()->create(), ['title' => 'Needle private', 'status' => 'completed']);
        config(['userpanel.orders_per_page' => 1]);

        Livewire::actingAs($user)
            ->test(MyOrders::class)
            ->set('search', 'Needle')
            ->set('status', 'completed')
            ->assertViewHas('orders', fn ($orders) => $orders->total() === 2)
            ->assertDontSee('Needle private')->assertDontSee('Needle excluded');
    }

    public function test_zero_is_a_valid_order_search_term(): void
    {
        $user = User::factory()->create();
        $this->order($user, ['order_number' => 'ORD-ABC', 'title' => 'Unrelated']);
        $this->order($user, ['order_number' => 'ORD-XYZ', 'title' => 'Project 0']);

        Livewire::actingAs($user)
            ->test(MyOrders::class)
            ->set('search', '0')
            ->assertSee('Project 0')->assertDontSee('Unrelated')
            ->assertViewHas('orders', fn ($orders) => $orders->total() === 1);
    }

    public function test_foreign_and_missing_orders_are_not_found(): void
    {
        $otherOrder = $this->order(User::factory()->create());
        $this->actingAs(User::factory()->create());

        $this->get('/dashboard/orders/'.$otherOrder->order_number)->assertNotFound();
        $this->get('/dashboard/orders/ORD-MISSING')->assertNotFound();
    }

    public function test_order_details_render_tasks_payment_states_and_escaped_content(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user, ['description' => '<script>alert(1)</script>', 'paid_amount' => 1500, 'deadline' => now()->addDays(2)]);
        $order->tasks()->create(['title' => 'Delivered task', 'status' => 'done', 'progress' => 100]);
        foreach (['completed', 'pending', 'failed', 'refunded'] as $status) {
            $order->payments()->create(['user_id' => $user->id, 'amount' => 100, 'status' => $status]);
        }

        Livewire::actingAs($user)
            ->test(OrderDetails::class, ['orderNumber' => $order->order_number])
            ->assertOk()
            ->assertSee('Delivered task')->assertSee('<script>alert(1)</script>')
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('ناموفق')->assertSee('بازپرداخت شده')
            ->assertSee('--progress:100%; width: 100%', false)
            ->assertDontSee('width: 150%', false);

        Livewire::actingAs($user)->test(MyOrders::class)
            ->assertDontSee('width: 150%', false);
    }

    public function test_zero_amount_order_without_tasks_or_payments_renders(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user, ['amount' => 0]);

        Livewire::actingAs($user)
            ->test(OrderDetails::class, ['orderNumber' => $order->order_number])->assertOk();
        Livewire::actingAs($user)->test(MyOrders::class)->assertOk();
    }

    // ===== تیکت‌ها (ماژول Tickets) =====

    public function test_ticket_list_filters_and_paginates_only_owned_records(): void
    {
        $user = User::factory()->create();
        $ticket = $this->ticket($user, ['status' => 'waiting']);
        $this->ticket($user, ['status' => 'waiting']);
        $this->ticket($user, ['subject' => 'Closed excluded', 'status' => 'closed']);
        $this->ticket(User::factory()->create(), ['subject' => 'Private excluded', 'status' => 'waiting']);
        $ticket->replies()->create(['user_id' => $user->id, 'message' => 'Existing reply', 'is_staff_reply' => false]);
        config(['userpanel.tickets_per_page' => 1]);

        Livewire::actingAs($user)
            ->test(MyTickets::class)
            ->set('status', 'waiting')
            ->assertDontSee('Closed excluded')->assertDontSee('Private excluded')
            ->assertViewHas('tickets', fn ($tickets) => $tickets->total() === 2);
    }

    public function test_ticket_creation_uses_current_user_and_safe_defaults(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Livewire::actingAs($user)
            ->test(TicketConversation::class, ['create' => true])
            ->set('subject', 'New support request')
            ->set('message', 'Please help with my project.')
            ->set('priority', 'urgent')
            ->set('category', 'billing')
            ->call('saveTicket');

        $ticket = Ticket::sole();
        $this->assertSame($user->id, $ticket->user_id);
        $this->assertSame('open', $ticket->status);
        // first_name: user_id/status همیشه از کاربر جاری و مقدار امن تنظیم می‌شود (نه mass-assignment)
        // priority/category ورودی قانونی فرم هستند
        $this->assertSame('urgent', $ticket->priority);
        $this->assertSame('billing', $ticket->category);
        $this->assertDatabaseCount('ticket_replies', 0);
    }

    public function test_ticket_creation_validation_displays_errors_without_creating(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(TicketConversation::class, ['create' => true])
            ->set('subject', 'x')
            ->set('message', 'short')
            ->call('saveTicket')
            ->assertHasErrors(['subject', 'message']);

        Livewire::test(TicketConversation::class, ['create' => true])
            ->set('subject', str_repeat('x', 191))
            ->set('message', str_repeat('x', 3001))
            ->set('priority', 'invalid')
            ->set('category', 'invalid')
            ->call('saveTicket');

        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_foreign_missing_and_malformed_ticket_ids_cannot_be_read(): void
    {
        $ticket = $this->ticket(User::factory()->create());
        $this->actingAs(User::factory()->create());

        foreach ([$ticket->id, 999999, 'invalid', $ticket->id.'e0', $ticket->id.'.0'] as $id) {
            $this->get('/dashboard/tickets/'.$id)->assertNotFound();
        }

        $this->assertDatabaseCount('ticket_replies', 0);
        $this->assertSame('open', $ticket->fresh()->status);
    }

    public function test_owner_reply_is_non_staff_and_moves_ticket_to_in_progress(): void
    {
        $user = User::factory()->create();
        $ticket = $this->ticket($user, ['status' => 'waiting']);

        Livewire::actingAs($user)
            ->test(TicketConversation::class, ['ticketId' => (string) $ticket->id])
            ->set('newReply', 'My new reply')
            ->call('reply');

        $reply = $ticket->replies()->sole();
        $this->assertSame($user->id, $reply->user_id);
        $this->assertFalse($reply->is_staff_reply);
        $this->assertSame('in_progress', $ticket->fresh()->status);
    }

    public function test_invalid_replies_do_not_mutate_the_ticket(): void
    {
        $user = User::factory()->create();
        $ticket = $this->ticket($user, ['status' => 'waiting']);

        foreach (['', 'x', str_repeat('x', 3001)] as $message) {
            Livewire::actingAs($user)
                ->test(TicketConversation::class, ['ticketId' => (string) $ticket->id])
                ->set('newReply', $message)
                ->call('reply')
                ->assertHasErrors('newReply');
        }

        $this->assertDatabaseCount('ticket_replies', 0);
        $this->assertSame('waiting', $ticket->fresh()->status);
    }

    public function test_owner_can_close_idempotently_but_cannot_reply_to_a_closed_ticket(): void
    {
        $user = User::factory()->create();
        $ticket = $this->ticket($user);

        for ($i = 0; $i < 2; $i++) {
            Livewire::actingAs($user)
                ->test(TicketConversation::class, ['ticketId' => (string) $ticket->id])
                ->call('closeTicket');
        }

        $this->assertSame('closed', $ticket->fresh()->status);
        $this->assertDatabaseCount('ticket_replies', 0);

        Livewire::actingAs($user)
            ->test(TicketConversation::class, ['ticketId' => (string) $ticket->id])
            ->set('newReply', 'Attempt to reopen')
            ->call('reply')
            ->assertStatus(404);
    }

    public function test_ticket_conversation_renders_user_and_staff_messages_escaped(): void
    {
        $user = User::factory()->create();
        $ticket = $this->ticket($user, ['message' => '<script>original()</script>']);
        app(\Modules\Tickets\Services\TicketService::class)->addReply($ticket, $user->id, 'Owner response', false);
        app(\Modules\Tickets\Services\TicketService::class)->addReply($ticket, User::factory()->create()->id, '<script>staff()</script>', true);

        Livewire::actingAs($user)
            ->test(TicketConversation::class, ['ticketId' => (string) $ticket->id])
            ->assertSee('Owner response')->assertSee('پشتیبانی')
            ->assertSee('<script>original()</script>')->assertSee('<script>staff()</script>')
            ->assertDontSee('<script>original()</script>', false)->assertDontSee('<script>staff()</script>', false);
    }

    // ===== سرویس (منطق منتقل‌شده به ماژول Tickets) =====

    public function test_service_rejects_closing_another_users_ticket(): void
    {
        $ticket = $this->ticket(User::factory()->create());
        $this->expectException(ModelNotFoundException::class);
        app(UserTicketService::class)->closeTicket(User::factory()->create(), $ticket);
    }

    public function test_service_checks_current_ticket_status_before_replying(): void
    {
        $user = User::factory()->create();
        $ticket = $this->ticket($user);
        Ticket::whereKey($ticket->id)->update(['status' => 'closed']);

        try {
            app(UserTicketService::class)->replyTicket($user, $ticket, 'Stale reply');
            $this->fail('Closed tickets must reject replies.');
        } catch (HttpException $exception) {
            // قانون پروژه: بجای 403 فقط وضعیت 404 برمی‌گردد
            $this->assertSame(404, $exception->getStatusCode());
        }

        $this->assertDatabaseCount('ticket_replies', 0);
        $this->assertSame('closed', $ticket->fresh()->status);
    }

    public function test_service_rejects_reply_to_another_users_ticket(): void
    {
        $ticket = $this->ticket(User::factory()->create());
        $this->expectException(ModelNotFoundException::class);
        app(UserTicketService::class)->replyTicket(User::factory()->create(), $ticket, 'Unauthorized');
    }
}
