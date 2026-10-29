<?php

namespace Modules\Consultation\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Modules\Consultation\Models\Consultant;
use Modules\Consultation\Models\ConsultationSlot;

/**
 * تولید اسلات‌های رزرو از زمان‌بندی هفتگی مشاور — مثل نوبت‌دهی مطب
 */
class ScheduleService
{
    /**
     * مدت هر اسلات (دقیقه) — از نوع مشاوره کوتاه‌تر خوانده می‌شود؛ اینجا فقط طول گرید
     */
    public const SLOT_GRANULARITY_MINUTES = 30;

    /**
     * تولید اسلات‌های بازه تاریخ برای مشاور بر اساس زمان‌بندی هفتگی‌اش
     * اسلات‌های قبلاً رزروشده دست نمی‌خورند (updateOrCreate با شرط is_booked)
     *
     * @param  array{0: CarbonImmutable, 1: CarbonImmutable}  $dateRange  [شروع, پایان]
     * @return int تعداد اسلات‌های ساخته/به‌روزشده
     */
    public function generateSlots(Consultant $consultant, array $dateRange): int
    {
        [$from, $to] = $dateRange;

        // زمان‌بندی فعال مشاور به تفکیک روز هفته (تقویم ایران: شنبه=0)
        $schedules = $consultant->schedules()
            ->where('is_active', true)
            ->get()
            ->groupBy('day_of_week');

        if ($schedules->isEmpty()) {
            return 0;
        }

        $created = 0;

        for ($date = $from->startOfDay(); $date->lte($to->startOfDay()); $date = $date->addDay()) {
            // Carbon: Sunday=0 ... Saturday=6 → تقویم ایران: Saturday=0 ... Friday=6
            $iranDay = ($date->dayOfWeek + 1) % 7;

            foreach ($schedules->get($iranDay, collect()) as $schedule) {
                $created += $this->generateSlotsForWindow(
                    $consultant,
                    $date->toDateString(),
                    $schedule->start_time,
                    $schedule->end_time
                );
            }
        }

        return $created;
    }

    /**
     * تولید اسلات‌های یک بازه زمانی در یک روز — با query builder (بدون cast)
     * تا فرمت date در lookup و insert همیشه یکسان باشد
     */
    public function generateSlotsForWindow(Consultant $consultant, string $date, string $startTime, string $endTime): int
    {
        $created = 0;
        $cursor = CarbonImmutable::parse($date.' '.$startTime);
        $end = CarbonImmutable::parse($date.' '.$endTime);

        while ($cursor->lt($end)) {
            $slotEnd = $cursor->addMinutes(self::SLOT_GRANULARITY_MINUTES);

            if ($slotEnd->gt($end)) {
                break;
            }

            $startTimeStr = $cursor->format('H:i:s');
            $endTimeStr = $slotEnd->format('H:i:s');

            $existing = DB::table('consultation_slots')
                ->where('consultant_id', $consultant->id)
                ->where('date', $date)
                ->where('start_time', $startTimeStr)
                ->first();

            if ($existing === null) {
                try {
                    DB::table('consultation_slots')->insert([
                        'consultant_id' => $consultant->id,
                        'date' => $date,
                        'start_time' => $startTimeStr,
                        'end_time' => $endTimeStr,
                        'is_booked' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $created++;
                } catch (\Illuminate\Database\UniqueConstraintViolationException) {
                    // race condition — اسلات همزمان توسط درخواست دیگری ساخته شد؛ skip
                }
            }

            $cursor = $slotEnd;
        }

        return $created;
    }
}