<?php

namespace Tests\Feature\Consultation;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Consultation\Models\Consultant;
use Modules\Consultation\Models\Consultation;
use Modules\Consultation\Models\ConsultationSlot;
use Modules\Consultation\Models\ConsultationType;
use Modules\Consultation\Services\ConsultationService;
use Modules\Consultation\Services\SlotService;
use Modules\SMS\Models\SmsLog;
use Tests\TestCase;

class ConsultationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(\Modules\SMS\Database\Seeders\SmsTemplateSeeder::class);

        $this->user = User::create([
            'name' => 'کاربر تست',
            'email' => 'user-cons@test.com',
            'phone' => '09121234567',
            'password' => 'password',
            'is_active' => true,
        ]);
    }

    private function consultantWithSchedule(): Consultant
    {
        $consultant = Consultant::create([
            'user_id' => User::create(['name' => 'مشاور تست', 'email' => 'consultant@test.com', 'password' => 'password', 'is_active' => true])->id,
            'specialty' => 'مشاور کسب‌وکار',
            'hourly_rate' => 500000,
            'rating' => 4.5,
            'is_active' => true,
        ]);

        foreach ([['09:00', '10:30'], ['14:00', '15:00']] as [$start, $end]) {
            \Modules\Consultation\Models\ConsultantSchedule::create([
                'consultant_id' => $consultant->id,
                'day_of_week' => 0, // شنبه
                'start_time' => $start,
                'end_time' => $end,
                'is_active' => true,
            ]);
        }

        return $consultant;
    }

    private function type(): ConsultationType
    {
        return ConsultationType::create([
            'name' => 'مشاوره آنلاین',
            'duration_minutes' => 30,
            'mode' => 'online',
            'price' => 300000,
            'is_active' => true,
        ]);
    }

    private function nextSaturday(): \Carbon\CarbonImmutable
    {
        return \Carbon\CarbonImmutable::today()->next(\Carbon\CarbonImmutable::SATURDAY);
    }

    // ===== ۵.۳ سرویس‌ها =====

    public function test_generate_slots_from_saturday_schedule(): void
    {
        $consultant = $this->consultantWithSchedule();
        $saturday = $this->nextSaturday();

        $count = app(\Modules\Consultation\Services\ScheduleService::class)->generateSlots($consultant, [$saturday, $saturday]);

        // ۹ تا ۱۰:۳۰ = ۳ اسلات + ۱۴ تا ۱۵ = ۲ اسلات
        $this->assertSame(5, $count);
        $this->assertDatabaseHas('consultation_slots', [
            'consultant_id' => $consultant->id,
            'date' => $saturday->toDateString(),
            'start_time' => '09:00:00',
            'is_booked' => false,
        ]);
    }

    public function test_available_slots_exclude_booked(): void
    {
        $consultant = $this->consultantWithSchedule();
        $saturday = $this->nextSaturday();
        app(\Modules\Consultation\Services\ScheduleService::class)->generateSlots($consultant, [$saturday, $saturday]);

        ConsultationSlot::where('consultant_id', $consultant->id)
            ->where('start_time', '09:00:00')
            ->update(['is_booked' => true, 'booked_at' => now()]);

        $slots = app(SlotService::class)->availableSlots($consultant, $saturday->toDateString());

        $this->assertNotContains('09:00', array_column($slots, 'start'));
        $this->assertCount(4, $slots);
    }

    public function test_book_creates_consultation_order_and_locks_slot(): void
    {
        $consultant = $this->consultantWithSchedule();
        $type = $this->type();
        $saturday = $this->nextSaturday();
        app(\Modules\Consultation\Services\ScheduleService::class)->generateSlots($consultant, [$saturday, $saturday]);
        $slot = ConsultationSlot::where('consultant_id', $consultant->id)->where('start_time', '09:00:00')->firstOrFail();

        $consultation = app(ConsultationService::class)->book($this->user, $consultant, $type, $slot, []);

        $this->assertSame('pending', $consultation->status);
        $this->assertNotNull($consultation->order_id);
        $this->assertTrue($consultation->slot->fresh()->is_booked);
        $this->assertSame('300000.00', (string) $consultation->order->amount);
    }

    // ===== SMS =====

    public function test_book_sends_sms_to_user_and_admin(): void
    {
        \Modules\Settings\Models\Setting::set('contact_phone', '09120000000', 'site');

        $consultant = $this->consultantWithSchedule();
        $type = $this->type();
        $saturday = $this->nextSaturday();
        app(\Modules\Consultation\Services\ScheduleService::class)->generateSlots($consultant, [$saturday, $saturday]);
        $slot = ConsultationSlot::where('consultant_id', $consultant->id)->where('start_time', '09:00:00')->firstOrFail();

        app(ConsultationService::class)->book($this->user, $consultant, $type, $slot, []);

        // ۲ پیامک: کاربر + مدیر
        $this->assertSame(2, SmsLog::count());
        $this->assertDatabaseHas('sms_logs', ['phone' => '09121234567']);
        $this->assertDatabaseHas('sms_logs', ['phone' => '09120000000']);

        $userSms = SmsLog::where('phone', '09121234567')->first();
        $this->assertStringContainsString('مشاور تست', $userSms->message);
        $adminSms = SmsLog::where('phone', '09120000000')->first();
        $this->assertStringContainsString('رزرو جدید', $adminSms->message);
    }

    public function test_double_booking_is_rejected(): void
    {
        $consultant = $this->consultantWithSchedule();
        $type = $this->type();
        $saturday = $this->nextSaturday();
        app(\Modules\Consultation\Services\ScheduleService::class)->generateSlots($consultant, [$saturday, $saturday]);
        $slot = ConsultationSlot::where('consultant_id', $consultant->id)->where('start_time', '09:00:00')->firstOrFail();

        app(ConsultationService::class)->book($this->user, $consultant, $type, $slot, []);

        $other = User::create(['name' => 'کاربر دوم', 'email' => 'u2@test.com', 'password' => 'password', 'is_active' => true]);

        $this->expectException(\RuntimeException::class);
        app(ConsultationService::class)->book($other, $consultant, $type, $slot->fresh(), []);
    }

    public function test_cancel_frees_the_slot(): void
    {
        $consultant = $this->consultantWithSchedule();
        $type = $this->type();
        $saturday = $this->nextSaturday();
        app(\Modules\Consultation\Services\ScheduleService::class)->generateSlots($consultant, [$saturday, $saturday]);
        $slot = ConsultationSlot::where('consultant_id', $consultant->id)->where('start_time', '09:00:00')->firstOrFail();

        $consultation = app(ConsultationService::class)->book($this->user, $consultant, $type, $slot, []);

        app(ConsultationService::class)->setStatus($consultation, \Modules\Consultation\Enums\ConsultationStatus::Cancelled);

        $this->assertSame('cancelled', $consultation->fresh()->status);
        $this->assertFalse($consultation->slot->fresh()->is_booked);
    }

    // ===== صفحات =====

    public function test_public_consultation_page_renders(): void
    {
        $this->get('/consultation')->assertOk()->assertSeeLivewire('consultation.booking-wizard');
    }

    public function test_book_page_requires_auth(): void
    {
        $this->get('/consultation/book')->assertRedirect('/login');
    }

    public function test_user_panel_consultations_page_renders(): void
    {
        $this->actingAs($this->user);

        // مشاوره‌های من داخل شل ریل‌تایم داشبورد رندر می‌شود (تب consultations)
        $response = $this->get('/dashboard?tab=consultations');

        if ($response->status() !== 200) {
            $response->dump();
        }

        $response->assertOk()->assertSeeLivewire('consultation.my-consultations');
    }

    public function test_admin_pages_require_permission(): void
    {
        $this->actingAs($this->user);

        $this->get('/admin/consultations')->assertNotFound();
        $this->get('/admin/consultations/consultants')->assertNotFound();
        $this->get('/admin/consultations/schedules')->assertNotFound();
    }

    public function test_admin_can_view_consultation_pages(): void
    {
        $admin = $this->makeAdmin('admin-cons@test.com', 'super-admin');
        $this->actingAs($admin, 'admin');

        $this->get('/admin/consultations')->assertOk()->assertSeeLivewire('consultation.admin.consultations-index');
        $this->get('/admin/consultations/consultants')->assertOk()->assertSeeLivewire('consultation.admin.consultants-manager');
        $this->get('/admin/consultations/schedules')->assertOk()->assertSeeLivewire('consultation.admin.schedules-manager');
    }

    // ===== ویزارد =====

    public function test_wizard_full_flow_books_and_sends_sms(): void
    {
        \Modules\Settings\Models\Setting::set('contact_phone', '09120000000', 'site');
        $this->actingAs($this->user);

        $consultant = $this->consultantWithSchedule();
        $this->type();
        $saturday = $this->nextSaturday();
        $saturdayDate = $saturday->toDateString();
        app(\Modules\Consultation\Services\ScheduleService::class)->generateSlots($consultant, [$saturday, $saturday]);

        Livewire::test(\Modules\Consultation\Livewire\Public\BookingWizard::class)
            ->call('chooseConsultant', $consultant->id)
            ->assertSet('step', 2)
            ->call('chooseType', ConsultationType::where('is_active', true)->first()->id)
            ->assertSet('step', 3)
            ->call('pickDate', $saturdayDate)
            ->call('chooseSlot', ConsultationSlot::where('date', $saturdayDate)->where('start_time', '09:00:00')->first()->id)
            ->call('nextStep')
            ->assertSet('step', 4)
            ->set('contactName', 'کاربر ویزارد')
            ->set('contactPhone', '09121112222')
            ->call('nextStep')
            ->assertSet('step', 5)
            ->call('confirmBooking')
            ->assertSet('step', 6);

        $consultation = Consultation::where('user_id', $this->user->id)->firstOrFail();
        $this->assertNotNull($consultation->order_id);
        $this->assertDatabaseHas('sms_logs', ['phone' => '09121112222']);
    }

    public function test_wizard_shows_no_slots_when_day_is_full(): void
    {
        $consultant = $this->consultantWithSchedule();
        $this->type();
        $saturday = $this->nextSaturday();
        $saturdayDate = $saturday->toDateString();
        app(\Modules\Consultation\Services\ScheduleService::class)->generateSlots($consultant, [$saturday, $saturday]);

        // همه اسلات‌ها رزرو می‌شوند
        ConsultationSlot::where('date', $saturdayDate)->update(['is_booked' => true, 'booked_at' => now()]);

        $component = Livewire::test(\Modules\Consultation\Livewire\Public\BookingWizard::class)
            ->call('chooseConsultant', $consultant->id)
            ->call('chooseType', ConsultationType::where('is_active', true)->first()->id)
            ->call('pickDate', $saturdayDate);

        // هیچ اسلات آزادی برای روز پر وجود ندارد
        $component->assertViewHas('slots', fn ($slots) => $slots === []);
    }
    // ===== پنل کاربر: درخواست مشاوره =====

    public function test_user_panel_booking_page_is_rendered(): void
    {
        $this->consultantWithSchedule();
        $this->type();

        $this->actingAs($this->user)
            ->get(route('user.consultations.create'))
            ->assertOk()
            ->assertSeeLivewire('consultation.consultation-booking');
    }

    public function test_user_panel_booking_full_flow(): void
    {
        $consultant = $this->consultantWithSchedule();
        $this->type();
        $saturday = $this->nextSaturday();
        $saturdayDate = $saturday->toDateString();
        app(\Modules\Consultation\Services\ScheduleService::class)->generateSlots($consultant, [$saturday, $saturday]);

        Livewire::actingAs($this->user)
            ->test(\Modules\Consultation\Livewire\UserPanel\ConsultationBooking::class)
            ->assertSet('step', 1)
            ->call('chooseType', ConsultationType::where('is_active', true)->first()->id)
            ->assertSet('step', 2)
            ->call('pickDate', $saturdayDate)
            ->call('chooseSlot', ConsultationSlot::where('date', $saturdayDate)->where('start_time', '09:00:00')->first()->id)
            ->call('nextStep')
            ->assertSet('step', 3)
            ->set('contactName', 'کاربر پنل')
            ->set('contactPhone', '09121113333')
            ->call('nextStep')
            ->assertSet('step', 4)
            ->call('confirmBooking')
            ->assertSet('step', 4);

        $consultation = Consultation::where('user_id', $this->user->id)->firstOrFail();
        $this->assertNotNull($consultation->order_id);
        $this->assertDatabaseHas('sms_logs', ['phone' => '09121113333']);
    }

    public function test_user_panel_booking_blocks_full_day(): void
    {
        $consultant = $this->consultantWithSchedule();
        $this->type();
        $saturday = $this->nextSaturday();
        $saturdayDate = $saturday->toDateString();
        app(\Modules\Consultation\Services\ScheduleService::class)->generateSlots($consultant, [$saturday, $saturday]);

        // همه اسلات‌ها رزرو شوند
        ConsultationSlot::where('date', $saturdayDate)->update(['is_booked' => true, 'booked_at' => now()]);

        Livewire::actingAs($this->user)
            ->test(\Modules\Consultation\Livewire\UserPanel\ConsultationBooking::class)
            ->call('chooseType', ConsultationType::where('is_active', true)->first()->id)
            ->call('pickDate', $saturdayDate)
            ->assertViewHas('slots', fn ($slots) => $slots === []);
    }
}
