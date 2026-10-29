<?php

namespace Modules\Consultation\Livewire\Admin;

use Livewire\Component;
use Modules\Consultation\Models\Consultant;
use Modules\Consultation\Models\ConsultantSchedule;
use Modules\Consultation\Models\ConsultationSlot;
use Modules\Consultation\Services\SlotService;

/**
 * زمان‌بندی هفتگی مشاوران — مثل مطب: هر روز چند بازه ساعت کاری
 * (مثلاً شنبه ۹-۱۲ و ۱۴-۱۸ = دو ردیف برای day_of_week=0)
 */
class SchedulesManager extends Component
{
    public ?int $consultantId = null;

    public bool $showForm = false;

    public ?int $editId = null;

    public array $form = [
        'day_of_week' => '0',
        'start_time' => '09:00',
        'end_time' => '12:00',
        'is_active' => true,
    ];

    public bool $successToast = false;

    public string $successMessage = '';

    public const DAYS = [
        0 => 'شنبه',
        1 => 'یکشنبه',
        2 => 'دوشنبه',
        3 => 'سه‌شنبه',
        4 => 'چهارشنبه',
        5 => 'پنجشنبه',
        6 => 'جمعه',
    ];

    public function updatedConsultantId(): void
    {
        $this->resetErrorBag();
        $this->showForm = false;
    }

    public function openCreateForm(): void
    {
        $this->resetErrorBag();
        $this->editId = null;
        $this->form = ['day_of_week' => '0', 'start_time' => '09:00', 'end_time' => '12:00', 'is_active' => true];
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $this->resetErrorBag();
        $schedule = ConsultantSchedule::findOrFail($id);

        $this->editId = $id;
        $this->form = [
            'day_of_week' => (string) $schedule->day_of_week,
            'start_time' => substr($schedule->start_time, 0, 5),
            'end_time' => substr($schedule->end_time, 0, 5),
            'is_active' => $schedule->is_active,
        ];
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'form.day_of_week' => ['required', 'integer', 'between:0,6'],
            'form.start_time' => ['required', 'date_format:H:i'],
            'form.end_time' => ['required', 'date_format:H:i', 'after:form.start_time'],
            'form.is_active' => ['boolean'],
        ], [
            'form.end_time.after' => 'ساعت پایان باید بعد از ساعت شروع باشد.',
        ]);

        ConsultantSchedule::updateOrCreate(
            ['id' => $this->editId],
            [
                'consultant_id' => $this->consultantId,
                'day_of_week' => (int) $this->form['day_of_week'],
                'start_time' => $this->form['start_time'],
                'end_time' => $this->form['end_time'],
                'is_active' => $this->form['is_active'],
            ]
        );

        $this->showForm = false;
        $this->notifySuccess('زمان‌بندی ذخیره شد. اسلات‌ها هنگام نمایش تقویم تولید می‌شوند.');
    }

    public function delete(int $id): void
    {
        ConsultantSchedule::findOrFail($id)->delete();
        $this->notifySuccess('بازه زمانی حذف شد.');
    }

    public function render()
    {
        $consultant = $this->consultantId ? Consultant::find($this->consultantId) : null;

        $schedules = $consultant
            ? ConsultantSchedule::where('consultant_id', $consultant->id)
                ->orderBy('day_of_week')->orderBy('start_time')->get()
            : collect();

        // تقویم شمسی ۱۴ روز آینده — از همان منبعی که پنل کاربران اسلات‌ها را می‌بیند
        $calendar = [];
        if ($consultant) {
            $calendar = app(SlotService::class)->calendarAvailability($consultant);

            $from = now()->toDateString();
            $to = now()->addDays(SlotService::CALENDAR_DAYS - 1)->toDateString();

            $booked = ConsultationSlot::where('consultant_id', $consultant->id)
                ->whereBetween('date', [$from, $to])
                ->where('is_booked', true)
                ->groupBy('date')
                ->selectRaw('date, COUNT(*) as booked_count')
                ->pluck('booked_count', 'date')
                ->all();

            foreach ($calendar as $key => $day) {
                $calendar[$key]['booked_count'] = (int) ($booked[$key] ?? 0);
            }
        }

        return view('consultation::livewire.admin.schedules-manager', [
            'consultants' => Consultant::with('user')->where('is_active', true)->get(),
            'schedules' => $schedules,
            'days' => self::DAYS,
            'calendar' => $calendar,
        ]);
    }

    private function notifySuccess(string $message): void
    {
        $this->successMessage = $message;
        $this->successToast = true;
    }
}