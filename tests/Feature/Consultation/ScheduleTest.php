<?php

namespace Tests\Feature\Consultation;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Consultation\Models\Consultant;
use Modules\Consultation\Models\ConsultationSlot;
use Modules\Consultation\Models\ConsultantSchedule;
use Modules\Consultation\Services\ScheduleService;
use Modules\Consultation\Services\SlotService;
use Tests\TestCase;

/**
 * تست‌های زمان‌بندی مشاور — تولید اسلات از برنامه هفتگی، idempotency و تقویم.
 */
class ScheduleTest extends TestCase
{
    use RefreshDatabase;

    protected ScheduleService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->service = app(ScheduleService::class);
    }

    private function consultant(array $schedules = []): Consultant
    {
        $consultant = Consultant::create([
            'user_id' => User::create([
                'name' => 'مشاور زمان‌بندی',
                'email' => 'sched-'.uniqid().'@test.com',
                'password' => 'password',
                'is_active' => true,
            ])->id,
            'specialty' => 'مشاور تست',
            'hourly_rate' => 500000,
            'is_active' => true,
        ]);

        foreach ($schedules as [$day, $start, $end]) {
            ConsultantSchedule::create([
                'consultant_id' => $consultant->id,
                'day_of_week' => $day,
                'start_time' => $start,
                'end_time' => $end,
                'is_active' => true,
            ]);
        }

        return $consultant;
    }

    private function nextSaturday(): \Carbon\CarbonImmutable
    {
        return \Carbon\CarbonImmutable::today()->next(\Carbon\CarbonImmutable::SATURDAY);
    }

    // ===== انتخاب مشاور در پنل مدیریت + تقویم شمسی =====

    public function test_schedules_manager_loads_consultant_after_selection(): void
    {
        $consultant = $this->consultant([[0, '09:00', '10:30']]); // شنبه

        // قبلاً wire:model بدون live بود و بعد از انتخاب مشاور هیچ اتفاقی نمی‌افتاد
        \Livewire\Livewire::test(\Modules\Consultation\Livewire\Admin\SchedulesManager::class)
            ->set('consultantId', $consultant->id)
            ->assertSet('consultantId', $consultant->id)
            ->assertSee('بازه کاری جدید')
            ->assertSee('تقویم شمسی')
            ->assertSee('شنبه')
            ->assertSee('۱۴۰۵'); // تاریخ شمسی در تقویم (سال جاری)
    }

    public function test_calendar_uses_jalali_labels_and_counts_booked_slots(): void
    {
        $consultant = $this->consultant([[0, '09:00', '10:30']]);

        // یک اسلات شنبه آینده می‌سازیم و رزرو می‌کنیم
        $saturday = $this->nextSaturday();
        $this->service->generateSlots($consultant, [$saturday, $saturday]);
        $slot = ConsultationSlot::where('consultant_id', $consultant->id)
            ->where('date', $saturday->toDateString())->first();
        $slot->update(['is_booked' => true]);

        $calendar = app(SlotService::class)->calendarAvailability($consultant);
        $saturdayEntry = $calendar[$saturday->toDateString()];

        $this->assertNotEmpty($saturdayEntry['display_day']);
        $this->assertNotEmpty($saturdayEntry['display_month']);
        // شمسی با ارقام فارسی: ۱۴۰۵/۰۷/۲۴
        $this->assertMatchesRegularExpression('/[۰-۹]{4}\/[۰-۹]{1,2}\/[۰-۹]{1,2}/u', $saturdayEntry['jalali_date']);
        $this->assertSame('شنبه', $saturdayEntry['day_label']);
    }

    // ===== تولید اسلات =====

    public function test_generate_slots_from_single_window(): void
    {
        $consultant = $this->consultant([[0, '09:00', '10:30']]); // شنبه
        $saturday = $this->nextSaturday();

        $count = $this->service->generateSlots($consultant, [$saturday, $saturday]);

        // ۹ تا ۱۰:۳۰ با گام ۳۰ دقیقه = ۳ اسلات
        $this->assertSame(3, $count);
    }

    public function test_generate_slots_multiple_windows_same_day(): void
    {
        $consultant = $this->consultant([[0, '09:00', '10:30'], [0, '14:00', '15:00']]);
        $saturday = $this->nextSaturday();

        $count = $this->service->generateSlots($consultant, [$saturday, $saturday]);

        // ۳ اسلات صبح + ۲ اسلات بعدازظهر = ۵
        $this->assertSame(5, $count);
    }

    public function test_generate_slots_respects_granularity(): void
    {
        $consultant = $this->consultant([[0, '09:00', '10:00']]);
        $saturday = $this->nextSaturday();

        $this->assertSame(2, $this->service->generateSlots($consultant, [$saturday, $saturday]));
    }

    public function test_generate_slots_is_idempotent(): void
    {
        $consultant = $this->consultant([[0, '09:00', '10:30']]);
        $saturday = $this->nextSaturday();

        $first = $this->service->generateSlots($consultant, [$saturday, $saturday]);
        $second = $this->service->generateSlots($consultant, [$saturday, $saturday]);

        $this->assertSame(3, $first);
        $this->assertSame(0, $second);

        $this->assertDatabaseCount('consultation_slots', 3);
    }

    public function test_generate_slots_across_date_range(): void
    {
        $consultant = $this->consultant([[0, '09:00', '10:00']]);
        $saturday = $this->nextSaturday();

        $count = $this->service->generateSlots($consultant, [$saturday, $saturday->addWeek()]);

        $this->assertSame(4, $count);
    }

    public function test_generate_slots_no_schedule_returns_zero(): void
    {
        $consultant = $this->consultant([]);
        $saturday = $this->nextSaturday();

        $this->assertSame(0, $this->service->generateSlots($consultant, [$saturday, $saturday]));
    }

    public function test_generate_slots_for_window_directly(): void
    {
        $consultant = $this->consultant();
        $date = $this->nextSaturday()->toDateString();

        $count = $this->service->generateSlotsForWindow($consultant, $date, '09:00', '10:30');

        $this->assertSame(3, $count);
        $this->assertDatabaseHas('consultation_slots', [
            'consultant_id' => $consultant->id,
            'date' => $date,
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'is_booked' => false,
        ]);
    }

    public function test_generate_slots_for_window_is_idempotent(): void
    {
        $consultant = $this->consultant();
        $date = $this->nextSaturday()->toDateString();

        $first = $this->service->generateSlotsForWindow($consultant, $date, '09:00', '10:00');
        $second = $this->service->generateSlotsForWindow($consultant, $date, '09:00', '10:00');

        $this->assertSame(2, $first);
        $this->assertSame(0, $second);
    }

    // ===== نگاشت روز هفته (تقویم ایران) =====

    public function test_iranian_day_mapping_saturday_is_zero(): void
    {
        $saturday = $this->nextSaturday();

        $this->assertSame(0, ($saturday->dayOfWeek + 1) % 7);
    }

    public function test_schedule_on_friday_does_not_generate_on_saturday(): void
    {
        // جمعه = day_of_week ۵ در تقویم ایران
        $consultant = $this->consultant([[5, '09:00', '10:00']]);
        $saturday = $this->nextSaturday();

        $count = $this->service->generateSlots($consultant, [$saturday, $saturday]);

        $this->assertSame(0, $count);
    }
}
