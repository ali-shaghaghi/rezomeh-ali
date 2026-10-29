<?php

namespace Tests\Feature\Projects;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Orders\Models\Order;
use Modules\ProjectDev\Database\Seeders\ProjectDevContentSeeder;
use Modules\ProjectDev\Livewire\Public\ProjectRequestForm;
use Modules\ProjectDev\Models\ProjectType;
use Modules\ProjectDev\Services\ProjectService;
use Tests\TestCase;

/**
 * تست‌های ماژول Projects — کاتالوگ قیمت‌گذاری، ثبت درخواست، بررسی ادمین و قسط‌های 30/30/40
 */
class ProjectsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected ProjectType $type;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(ProjectDevContentSeeder::class);
        $this->seed(\Modules\SMS\Database\Seeders\SmsTemplateSeeder::class);

        $this->user = User::factory()->create([
            'is_active' => true,
            'phone' => '09120000000',
        ]);
        $this->type = ProjectType::where('name', 'طراحی سایت فروشگاهی (لاراول)')->firstOrFail();
    }

    // ===== تست Service =====

    public function test_estimate_price_is_base_plus_selected_options(): void
    {
        $service = app(ProjectService::class);

        $optionIds = $this->type->options()->take(2)->pluck('id')->all();
        $expected = (float) $this->type->base_price + $this->type->options()->whereIn('id', $optionIds)->sum('price');

        $this->assertSame($expected, $service->estimatePrice($this->type, $optionIds));
    }

    public function test_user_can_create_project_request_with_snapshot(): void
    {
        $service = app(ProjectService::class);

        $optionIds = $this->type->options()->take(1)->pluck('id')->all();

        $request = $service->createRequest($this->user, [
            'title' => 'فروشگاه پوشاک',
            'project_type_id' => $this->type->id,
            'description' => 'شرح پروژه',
            'option_ids' => $optionIds,
            'custom_options' => [['name' => 'چت آنلاین', 'note' => 'با هوش مصنوعی']],
        ]);

        $this->assertDatabaseHas('project_requests', ['id' => $request->id, 'user_id' => $this->user->id, 'status' => 'pending']);
        $this->assertSame('چت آنلاین', $request->custom_options[0]['name']);
        $this->assertSame((float) $this->type->base_price + $this->type->options()->whereIn('id', $optionIds)->sum('price'), (float) $request->estimated_price);
    }

    public function test_admin_review_sets_final_price_and_convert_creates_order_with_30_30_40(): void
    {
        $service = app(ProjectService::class);

        $request = $service->createRequest($this->user, [
            'title' => 'پروژه قسطی',
            'project_type_id' => $this->type->id,
        ]);

        // ثبت بررسی ادمین
        $reviewed = $service->review($request, [
            'final_price' => 10000000,
            'difficulty' => 'high',
            'estimated_days' => 60,
            'deadline' => now()->addDays(60)->toDateString(),
            'status' => 'approved',
        ]);
        $this->assertSame('approved', $reviewed->status);
        $this->assertSame(10000000.0, (float) $reviewed->final_price);

        // تبدیل به سفارش + قسط‌های ۳۰/۳۰/۴۰
        $order = $service->convertToOrder($reviewed, ['amount' => 10000000]);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertSame(10000000.0, (float) $order->amount);
        $this->assertSame('converted', $reviewed->fresh()->status);
        $this->assertSame($order->id, $reviewed->fresh()->order_id);

        $this->assertSame(3, $order->installments()->count());

        $first = $order->installments()->where('installment_number', 1)->first();
        $second = $order->installments()->where('installment_number', 2)->first();
        $third = $order->installments()->where('installment_number', 3)->first();

        $this->assertSame(3000000.0, (float) $first->amount);
        $this->assertSame(3000000.0, (float) $second->amount);
        $this->assertSame(4000000.0, (float) $third->amount);
        $this->assertSame(30, $first->percent);
        $this->assertSame(30, $second->percent);
        $this->assertSame(40, $third->percent);
    }

    // ===== تست Livewire (فرم کاربر) =====

    public function test_user_panel_request_form_computes_live_price(): void
    {
        $this->actingAs($this->user);

        $optionIds = $this->type->options()->pluck('id')->all();

        Livewire::test(ProjectRequestForm::class)
            ->call('selectType', $this->type->id)
            ->assertSet('form.project_type_id', $this->type->id)
            ->call('toggleOption', $optionIds[0])
            ->call('addCustomOption')
            ->assertHasErrors(['customOptionForm.name'])
            ->set('customOptionForm.name', 'چت آنلاین')
            ->call('addCustomOption')
            ->assertCount('customOptions', 1);
    }

    public function test_user_panel_request_form_submits_request(): void
    {
        $this->actingAs($this->user);

        $optionIds = $this->type->options()->pluck('id')->all();

        Livewire::test(ProjectRequestForm::class)
            ->set('form.title', 'فروشگاه اینترنتی من')
            ->set('form.project_type_id', $this->type->id)
            ->set('selectedOptionIds', [$optionIds[0]])
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('project_requests', 1);
        $this->assertDatabaseHas('project_requests', ['user_id' => $this->user->id, 'title' => 'فروشگاه اینترنتی من', 'status' => 'pending']);
    }

    public function test_user_projects_page_renders(): void
    {
        $this->actingAs($this->user);

        $this->get(route('user.projectdev.create'))->assertOk();
        $this->get(route('user.dashboard'))->assertOk();
    }

    public function test_user_can_pay_first_installment_via_gateway_page(): void
    {
        $service = app(ProjectService::class);

        $request = $service->createRequest($this->user, [
            'title' => 'پروژه پرداخت',
            'project_type_id' => $this->type->id,
        ]);
        $reviewed = $service->review($request, ['final_price' => 10000000, 'status' => 'approved']);
        $order = $service->convertToOrder($reviewed, ['amount' => 10000000]);
        $first = $order->installments()->where('installment_number', 1)->first();

        // شروع پرداخت قسط اول
        $this->actingAs($this->user)
            ->get(route('user.projectdev.payment.pay', $first->id))
            ->assertRedirect();

        // پرداخت pending ثبت شد
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseHas('payments', ['status' => 'pending', 'amount' => 3000000]);

        // صفحه شبیه‌ساز درگاه
        $payment = \Modules\Orders\Models\Payment::first();
        $this->get(route('user.projectdev.payment.gateway', $payment->id))->assertOk();

        // تایید پرداخت → قسط اول بسته و مبلغ سفارش به‌روز می‌شود
        $this->post(route('user.projectdev.payment.complete', $payment->id))
            ->assertRedirect(route('user.orders.show', $order->order_number));

        $this->assertDatabaseHas('order_installments', ['id' => $first->id, 'status' => 'paid']);
        $this->assertSame(3000000.0, (float) $order->fresh()->paid_amount);

        // قسط دوم قبل از اول قابل پرداخت نیست — ولی حالا اول بسته شده
        $second = $order->installments()->where('installment_number', 2)->first();
        $this->actingAs($this->user)
            ->get(route('user.projectdev.payment.pay', $second->id))
            ->assertRedirect();
    }

    public function test_guest_and_other_users_cannot_pay_someone_elses_installment(): void
    {
        $other = User::factory()->create(['is_active' => true]);
        $service = app(ProjectService::class);

        $request = $service->createRequest($this->user, [
            'title' => 'پروژه دیگران',
            'project_type_id' => $this->type->id,
        ]);
        $reviewed = $service->review($request, ['final_price' => 5000000, 'status' => 'approved']);
        $order = $service->convertToOrder($reviewed, ['amount' => 5000000]);
        $first = $order->installments()->where('installment_number', 1)->first();

        // کاربر دیگر
        $this->actingAs($other)
            ->get(route('user.projectdev.payment.pay', $first->id))
            ->assertNotFound();

        // مهمان → ریدایرکت به ورود
        $this->post(route('auth.logout'));
        $this->get(route('user.projectdev.payment.pay', $first->id))
            ->assertRedirect(route('auth.login'));
    }

    // ===== تست SMS اطلاع‌رسانی (CHECKPOINT ۸) =====

    /**
     * وقتی کاربر درخواست پروژه ثبت می‌کند، پیامک «ثبت درخواست» برایش ارسال می‌شود.
     * در محیط تست درایور لاگ فعال است → پیامک در جدول sms_logs ثبت می‌شود.
     */
    public function test_sms_sent_when_user_submits_project_request(): void
    {
        $service = app(ProjectService::class);

        $service->createRequest($this->user, [
            'title' => 'پروژه پیامکی',
            'project_type_id' => $this->type->id,
        ]);

        $this->assertDatabaseHas('sms_logs', [
            'user_id' => $this->user->id,
            'phone' => $this->user->phone,
        ]);

        // محتوای پیامک از قالب project_request_submitted می‌آید
        $log = \Modules\SMS\Models\SmsLog::where('user_id', $this->user->id)->first();
        $this->assertStringContainsString('پروژه پیامکی', $log->message);
        $this->assertStringContainsString('درخواست پروژه', $log->message);
    }

    /**
     * تایید درخواست توسط ادمین → پیامک «ارسال پیشنهاد» با قیمت نهایی و مدت زمان
     */
    public function test_sms_proposal_sent_when_admin_approves_request(): void
    {
        $service = app(ProjectService::class);

        $request = $service->createRequest($this->user, [
            'title' => 'پروژه پیشنهاد',
            'project_type_id' => $this->type->id,
        ]);

        // پاک کردن پیامک ثبت‌نام برای ایزوله کردن پیامک پیشنهاد
        \Modules\SMS\Models\SmsLog::query()->delete();

        $service->review($request, [
            'final_price' => 8500000,
            'estimated_days' => 45,
            'status' => 'approved',
        ]);

        $logs = \Modules\SMS\Models\SmsLog::all();
        $this->assertGreaterThan(0, $logs->count(), 'هیچ پیامکی پس از تایید ادمین ارسال نشد');

        $proposalLog = $logs->filter(fn ($l) => str_contains($l->message, 'پیشنهاد'))->first();
        $this->assertNotNull($proposalLog, 'پیامک «ارسال پیشنهاد پروژه» ارسال نشد — پیامک‌ها: '.$logs->pluck('message')->implode(' | '));
        $this->assertStringContainsString('8,500,000', $proposalLog->message);
        $this->assertStringContainsString('45', $proposalLog->message);
    }

    /**
     * تغییر وضعیت (رد کردن) → پیامک «تغییر وضعیت پروژه»
     */
    public function test_sms_status_changed_sent_when_request_rejected(): void
    {
        $service = app(ProjectService::class);

        $request = $service->createRequest($this->user, [
            'title' => 'پروژه رد شده',
            'project_type_id' => $this->type->id,
        ]);

        \Modules\SMS\Models\SmsLog::query()->delete();

        $service->review($request, ['status' => 'rejected', 'final_price' => 0]);

        $this->assertDatabaseHas('sms_logs', [
            'user_id' => $this->user->id,
            'phone' => $this->user->phone,
        ]);

        $log = \Modules\SMS\Models\SmsLog::first();
        $this->assertStringContainsString('پروژه رد شده', $log->message);
        $this->assertStringContainsString('رد شده', $log->message);
    }

    /**
     * تبدیل به سفارش → پیامک «تغییر وضعیت» (تبدیل به سفارش)
     */
    public function test_sms_sent_when_converted_to_order(): void
    {
        $service = app(ProjectService::class);

        $request = $service->createRequest($this->user, [
            'title' => 'پروژه تبدیلی',
            'project_type_id' => $this->type->id,
        ]);
        $reviewed = $service->review($request, ['final_price' => 10000000, 'status' => 'approved']);

        \Modules\SMS\Models\SmsLog::query()->delete();

        $service->convertToOrder($reviewed, ['amount' => 10000000]);

        $log = \Modules\SMS\Models\SmsLog::first();
        $this->assertNotNull($log, 'پیامک تبدیل به سفارش ارسال نشد');
        $this->assertStringContainsString('پروژه تبدیلی', $log->message);
    }

    /**
     * خرابی سرویس SMS نباید جریان اصلی را بشکند — ثبت درخواست حتی بدون قالب پیامک هم کار می‌کند.
     */
    public function test_sms_failure_does_not_break_request_creation(): void
    {
        // حذف همه قالب‌ها → send() پرتاب می‌کند که باید بلعیده شود
        \Modules\SMS\Models\SmsTemplate::query()->delete();

        $service = app(ProjectService::class);

        $request = $service->createRequest($this->user, [
            'title' => 'پروژه بدون قالب پیامک',
            'project_type_id' => $this->type->id,
        ]);

        $this->assertDatabaseHas('project_requests', ['id' => $request->id, 'status' => 'pending']);
        $this->assertDatabaseMissing('sms_logs', ['user_id' => $this->user->id]);
    }

    // ===== CHECKPOINT ۸: صفحات مدیریت + فرم درخواست =====

    /**
     * صفحه مدیریت درخواست‌های پروژه برای ادمین باز می‌شود (Livewire ProjectRequestsIndex)
     */
    public function test_admin_project_requests_management_page_renders(): void
    {
        $admin = $this->makeAdmin();

        $service = app(ProjectService::class);
        $service->createRequest($this->user, [
            'title' => 'درخواست برای پنل ادمین',
            'project_type_id' => $this->type->id,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.projectdev.requests'))
            ->assertOk()
            ->assertSeeLivewire('admin.projectdev.requests')
            ->assertSee('درخواست برای پنل ادمین');
    }

    /**
     * کاربر عادی نمی‌تواند صفحه مدیریت درخواست‌ها را باز کند
     */
    public function test_unauthorized_admin_cannot_view_project_requests(): void
    {
        $this->actingAs($this->user)
            ->get(route('admin.projectdev.requests'))
            ->assertNotFound();
    }

    /**
     * ادمین می‌تواند از طریق Livewire درخواست را بررسی و تایید کند (شامل SMS پیشنهاد)
     */
    public function test_admin_can_review_request_via_livewire_with_sms(): void
    {
        $admin = $this->makeAdmin();

        $service = app(ProjectService::class);
        $request = $service->createRequest($this->user, [
            'title' => 'بررسی Livewire',
            'project_type_id' => $this->type->id,
        ]);

        \Modules\SMS\Models\SmsLog::query()->delete();

        Livewire::actingAs($admin, 'admin')
            ->test(\Modules\ProjectDev\Livewire\Admin\ProjectRequestsIndex::class)
            ->call('openReview', $request->id)
            ->assertSet('reviewModalOpen', true)
            ->set('reviewForm.final_price', 7500000)
            ->set('reviewForm.estimated_days', 20)
            ->call('saveReview')
            ->assertHasNoErrors();

        $this->assertSame('approved', $request->fresh()->status);

        // پیامک پیشنهاد قیمت ارسال شده
        $this->assertDatabaseHas('sms_logs', ['user_id' => $this->user->id]);
        $this->assertStringContainsString('7,500,000', \Modules\SMS\Models\SmsLog::first()->message);
    }

    /**
     * فرم درخواست پروژه برای کاربر باز می‌شود (CHECKPOINT ۸: /projects/request)
     */
    public function test_project_request_form_page_renders(): void
    {
        $this->actingAs($this->user)
            ->get(route('user.projectdev.create'))
            ->assertOk()
            ->assertSeeLivewire('public.projectdev.request-form');
    }
}
