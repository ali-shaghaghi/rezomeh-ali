<?php

namespace Modules\Consultation\Services;

use Carbon\CarbonImmutable;
use Modules\Consultation\Models\Consultant;
use Modules\Consultation\Models\ConsultationSlot;
use Modules\Consultation\Models\ConsultationType;

/**
 * اسلات‌های آزاد برای نمایش در ویزارد رزرو
 */
class SlotService
{
    /**
     * چند روز جلوتر تقویم نمایش داده شود
     */
    public const CALENDAR_DAYS = 14;

    /**
     * اسلات‌های آزاد یک مشاور در یک تاریخ (فقط آینده، اسلات ردشده حذف)
     */
    public function availableSlots(Consultant $consultant, string $date): array
    {
        $this->ensureSlotsGenerated($consultant, $date);

        $now = now();

        return ConsultationSlot::query()
            ->where('consultant_id', $consultant->id)
            ->where('date', $date)
            ->where('is_booked', false)
            ->get()
            // اسلات‌هایی که زمانشان گذشته رد می‌شوند (date به‌خاطر cast آبجکت Carbon است)
            ->filter(fn (ConsultationSlot $slot) => CarbonImmutable::parse($slot->date->toDateString().' '.$slot->start_time)->gt($now))
            ->values()
            ->map(fn (ConsultationSlot $slot) => [
                'id' => $slot->id,
                'start' => substr($slot->start_time, 0, 5),
                'end' => substr($slot->end_time, 0, 5),
            ])
            ->all();
    }

    /**
     * نقشه روزهای تقویم: هر روز چند اسلات آزاد دارد (برای نمایش «خالی/موجود»)
     *
     * @return array<string, int> date => free count
     */
    public function calendarAvailability(Consultant $consultant): array
    {
        $from = CarbonImmutable::today();
        $to = $from->addDays(self::CALENDAR_DAYS - 1);

        $this->ensureSlotsGenerated($consultant, $from->toDateString(), $to->toDateString());

        $free = ConsultationSlot::query()
            ->where('consultant_id', $consultant->id)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->where('is_booked', false)
            ->whereRaw("CONCAT(date, ' ', start_time) > ?", [$now = now()->format('Y-m-d H:i:s')])
            ->groupBy('date')
            ->selectRaw('date, COUNT(*) as free_count')
            ->pluck('free_count', 'date');

        $calendar = [];
        $isFa = current_locale() === 'fa';

        for ($date = $from; $date->lte($to); $date = $date->addDay()) {
            $key = $date->toDateString();
            $calendar[$key] = [
                'date' => $key,
                'day_label' => \Modules\Consultation\Models\ConsultantSchedule::dayLabelFor(($date->dayOfWeek + 1) % 7),
                'day_number' => $date->day,
                'free_count' => (int) ($free[$key] ?? 0),
                // نمایش تقویم شمسی برای کاربران فارسی (برای en همان میلادی)
                'display_day' => $isFa ? jdate('j', $date) : (string) $date->day,
                'display_month' => $isFa ? jdate('F', $date) : $date->format('F'),
                'jalali_date' => jdate('Y/m/d', $date),
            ];
        }

        return $calendar;
    }

    /**
     * اطمینان از تولید اسلات‌ها برای بازه — idempotent
     */
    public function ensureSlotsGenerated(Consultant $consultant, string $from, ?string $to = null): void
    {
        app(ScheduleService::class)->generateSlots(
            $consultant,
            [CarbonImmutable::parse($from), CarbonImmutable::parse($to ?? $from)]
        );
    }
}