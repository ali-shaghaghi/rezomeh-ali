<?php

namespace Tests\Feature\SMS;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\SMS\Database\Seeders\SmsTemplateSeeder;
use Modules\SMS\Drivers\KavenegarDriver;
use Modules\SMS\Models\SmsLog;
use Modules\SMS\Models\SmsTemplate;
use Modules\SMS\Services\SmsService;
use Tests\TestCase;

class SmsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(SmsTemplateSeeder::class);
    }

    private function admin(): \App\Models\Admin
    {
        // ادمین از جدول جدا (گارد admin)
        return $this->makeAdmin('sms-admin@test.com', 'super-admin');
    }

    // ===== صفحات ادمین =====

    public function test_guest_is_redirected_from_sms_pages(): void
    {
        $this->get('/admin/sms')->assertRedirect('/admin/login');
        $this->get('/admin/sms/logs')->assertRedirect('/admin/login');
        $this->get('/admin/sms/templates')->assertRedirect('/admin/login');
    }

    public function test_admin_can_view_sms_pages(): void
    {
        $this->actingAs($this->admin(), 'admin');

        $this->get('/admin/sms')->assertOk()->assertSeeLivewire('sms.settings');
        $this->get('/admin/sms/logs')->assertOk()->assertSeeLivewire('sms.logs');
        $this->get('/admin/sms/templates')->assertOk()->assertSeeLivewire('sms.templates');
    }

    public function test_plain_user_cannot_access_sms_pages(): void
    {
        $user = User::create(['name' => 'Plain', 'email' => 'plain-sms@test.com', 'password' => 'password', 'is_active' => true]);
        $user->roles()->attach(\Modules\Core\Models\Role::where('slug', 'user')->firstOrFail());
        $this->actingAs($user);

        // قانون پروژه: دسترسی غیرمجاز = 404
        $this->get('/admin/sms')->assertNotFound();
        $this->get('/admin/sms/logs')->assertNotFound();
        $this->get('/admin/sms/templates')->assertNotFound();
    }

    // ===== ارسال با قالب =====

    public function test_send_sms_renders_template_and_logs(): void
    {
        $log = send_sms('09121234567', 'consultation_booked', [
            'name' => 'علی',
            'date' => '۱۴۰۵/۰۱/۰۱',
            'time' => '۱۰:۰۰',
        ]);

        $this->assertInstanceOf(SmsLog::class, $log);
        $this->assertSame('09121234567', $log->phone);
        $this->assertStringContainsString('علی عزیز', $log->message);
        $this->assertStringContainsString('۱۴۰۵/۰۱/۰۱', $log->message);
        $this->assertSame('sent', $log->status);
        $this->assertSame('log', $log->provider);
        $this->assertNotNull($log->sent_at);
        $this->assertDatabaseCount('sms_logs', 1);
    }

    public function test_send_sms_with_unknown_template_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        send_sms('09121234567', 'nonexistent_template');
    }

    public function test_send_sms_logs_to_laravel_log_with_log_driver(): void
    {
        Log::spy();

        send_sms('09121234567', 'otp_code', ['code' => '12345', 'minutes' => 2]);

        Log::shouldHaveReceived('info')
            ->once()
            ->withArgs(fn (string $message) => str_contains($message, 'SMS [LogDriver] To: 09121234567')
                && str_contains($message, '12345'));
    }

    public function test_disabled_sms_still_logs_via_log_driver(): void
    {
        // پیش‌فرض: sms_is_enabled = false → ارسال واقعی انجام نمی‌شود ولی لاگ ثبت می‌شود
        $log = send_sms_raw('09120000000', 'پیام خام تست');

        $this->assertSame('log', $log->provider);
        $this->assertSame('sent', $log->status);
        $this->assertSame('پیام خام تست', $log->message);
    }

    // ===== سرویس =====

    public function test_render_replaces_both_brace_styles(): void
    {
        $service = app(SmsService::class);

        $this->assertSame(
            'سلام علی، کد شما ۱۲۳ است',
            $service->render('سلام {name}، کد شما { code } است', ['name' => 'علی', 'code' => '۱۲۳'])
        );
    }

    public function test_template_seeder_created_default_templates(): void
    {
        $this->assertDatabaseHas('sms_templates', ['key' => 'consultation_booked', 'is_active' => true]);
        $this->assertDatabaseHas('sms_templates', ['key' => 'otp_code']);
        // 4 قالب پایه + 2 قالب مشاوره (consultation_booked_user/admin)
        $this->assertDatabaseCount('sms_templates', 9);
    }

    public function test_inactive_template_cannot_be_sent(): void
    {
        SmsTemplate::where('key', 'consultation_booked')->update(['is_active' => false]);

        $this->expectException(\InvalidArgumentException::class);

        send_sms('09121234567', 'consultation_booked');
    }

    public function test_send_bulk_creates_log_per_phone(): void
    {
        $sent = app(SmsService::class)->sendBulk(['09121111111', '09122222222', '09123333333'], 'پیام گروهی');

        $this->assertSame(3, $sent);
        $this->assertDatabaseCount('sms_logs', 3);
        $this->assertDatabaseHas('sms_logs', ['phone' => '09122222222', 'status' => 'sent']);
    }

    // ===== user_id باید فقط از جدول users بیاید (نه admins) =====

    public function test_sms_in_admin_context_does_not_write_admin_id_to_user_id(): void
    {
        // گارد admin فعال است (مثل پنل ادمین) — قبلاً auth()->id() ادمین را در
        // user_id می‌نوشت و به‌خاطر FK به users خطای 1452 می‌داد.
        $this->actingAs($this->admin(), 'admin');

        $log = send_sms_raw('09120000001', 'تست در پنل ادمین');

        $this->assertNull($log->user_id);
        $this->assertDatabaseHas('sms_logs', ['phone' => '09120000001', 'user_id' => null]);
    }

    public function test_sms_with_admin_as_user_param_does_not_crash(): void
    {
        // Admin از User ارث می‌برد ولی جدولش جداست — نباید به user_id راه یابد.
        $log = send_sms_raw('09120000002', 'تست با پارامتر ادمین', $this->admin());

        $this->assertNull($log->user_id);
    }

    public function test_sms_in_web_context_records_web_user_id(): void
    {
        $user = User::create(['name' => 'Web', 'email' => 'web-sms@test.com', 'password' => 'password', 'is_active' => true]);
        $user->roles()->attach(\Modules\Core\Models\Role::where('slug', 'user')->firstOrFail());
        $this->actingAs($user);

        $log = send_sms_raw('09120000003', 'تست با کاربر عادی');

        $this->assertSame($user->id, $log->user_id);
    }

    // ===== پارامتر sender کاوه‌نگار (خطای 412: ارسال کننده نامعتبر) =====

    public function test_kavenegar_omits_sender_param_when_no_sender_configured(): void
    {
        Http::fake([
            'api.kavenegar.com/*' => Http::response([
                'return' => ['status' => 200, 'message' => 'OK'],
                'entries' => [],
            ]),
        ]);

        (new KavenegarDriver('test-api-key'))->send('09121234567', 'پیام تست');

        // طبق مستندات کاوه‌نگار: بدون پارامتر sender → ارسال‌کننده پیش‌فرض حساب.
        Http::assertSent(function (Request $request) {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return str_contains($request->url(), 'send.json') && ! array_key_exists('sender', $query);
        });
    }

    public function test_kavenegar_sends_sender_param_when_configured(): void
    {
        Http::fake([
            'api.kavenegar.com/*' => Http::response([
                'return' => ['status' => 200, 'message' => 'OK'],
                'entries' => [],
            ]),
        ]);

        (new KavenegarDriver('test-api-key', '09123456789'))->send('09121234567', 'پیام تست');

        Http::assertSent(function (Request $request) {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return ($query['sender'] ?? null) === '09123456789';
        });
    }

    public function test_kavenegar_blank_sender_from_caller_is_omitted(): void
    {
        Http::fake([
            'api.kavenegar.com/*' => Http::response([
                'return' => ['status' => 412, 'message' => 'ارسال کننده نامعتبر است'],
                'entries' => null,
            ]),
        ]);

        $driver = new KavenegarDriver('test-api-key', '');
        $result = $driver->send('09121234567', 'پیام تست', '   ');

        Http::assertSent(function (Request $request) {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return ! array_key_exists('sender', $query);
        });
        $this->assertFalse($result['success']);
    }

    // ===== راهنمای خطا412 در پنل تنظیمات پیامک =====

    public function test_send_test_shows_412_sender_guidance(): void
    {
        Http::fake([
            'api.kavenegar.com/*' => Http::response([
                'return' => ['status' => 412, 'message' => 'ارسال کننده نامعتبر است'],
                'entries' => null,
            ]),
        ]);

        \Livewire\Livewire::test(\Modules\SMS\Livewire\Admin\SmsSettings::class)
            ->set('driver', 'kavenegar')
            ->set('testPhone', '09121234567')
            ->call('sendTest')
            ->assertSet('errorToast', true)
            ->assertSet('errorMessage', fn (string $msg) => str_contains($msg, 'خطوط و ارسال‌کننده‌ها'))
            ->assertSet('errorMessage', fn (string $msg) => str_contains($msg, '412'));

        // شکست باید تمیز در لاگ ثبت شود (بدون کرش)
        $this->assertDatabaseHas('sms_logs', [
            'phone' => '09121234567',
            'provider' => 'kavenegar',
            'status' => 'failed',
            'user_id' => null,
        ]);
    }

    // ===== مشاهده کامل لاگ (مودال ریل‌تایم) + منطقه زمانی =====

    public function test_logs_full_view_modal_opens_and_closes_without_reload(): void
    {
        $log = send_sms_raw('09120000010', 'متن کامل لاگ تست');

        \Livewire\Livewire::test(\Modules\SMS\Livewire\Admin\SmsLogs::class)
            ->call('viewLog', $log->id)
            ->assertSet('viewLogId', $log->id)
            ->assertSee('مشاهده کامل لاگ پیامک')
            ->assertSee('متن کامل لاگ تست')
            ->assertSee('wire:poll.5s')
            ->call('closeView')
            ->assertSet('viewLogId', null)
            ->assertDontSee('مشاهده کامل لاگ پیامک');
    }

    public function test_logs_view_rejects_unknown_log(): void
    {
        $component = \Livewire\Livewire::test(\Modules\SMS\Livewire\Admin\SmsLogs::class);

        $component->call('viewLog', 999999)->assertStatus(404);
    }

    public function test_app_timezone_is_tehran_so_log_times_match_iran(): void
    {
        // قبلاً UTC بود و ساعت لاگ‌ها ۳:۳۰ با ساعت ایران اختلاف داشت
        $this->assertSame('Asia/Tehran', config('app.timezone'));
        $this->assertSame('Asia/Tehran', now()->timezoneName);
    }

    public function test_send_test_shows_501_owner_only_guidance(): void
    {
        Http::fake([
            'api.kavenegar.com/*' => Http::response([
                'return' => ['status' => 501, 'message' => 'امکان ارسال پیامک فقط به شماره صاحب حساب داده شده است'],
                'entries' => null,
            ]),
        ]);

        \Livewire\Livewire::test(\Modules\SMS\Livewire\Admin\SmsSettings::class)
            ->set('driver', 'kavenegar')
            ->set('testPhone', '09121234567')
            ->call('sendTest')
            ->assertSet('errorToast', true)
            ->assertSet('errorMessage', fn (string $msg) => str_contains($msg, '501') && str_contains($msg, 'پیام تست'))
            ->assertSet('errorMessage', fn (string $msg) => str_contains($msg, 'محدودیت سمت کاوه‌نگار'));

        $this->assertDatabaseHas('sms_logs', ['phone' => '09121234567', 'status' => 'failed']);
    }
}