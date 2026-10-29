<?php

namespace Modules\Consultation\Livewire\UserPanel;

use Livewire\Component;
use Modules\Consultation\Models\Consultant;
use Modules\Consultation\Models\Consultation;
use Modules\Consultation\Models\ConsultationType;
use Modules\Consultation\Services\ConsultationService;
use Modules\Consultation\Services\SlotService;

/**
 * درخواست مشاوره از پنل کاربر — جدول زمان‌بندی زنده + مراحل کوتاه
 *
 * کاربر زمان‌های آزاد مشاور را هم‌زمان با انتخاب تاریخ می‌بیند (ریل‌تایم،
 * بدون رفرش صفحه) و قدم‌به‌قدم جلو می‌رود تا خسته نشود.
 */
class ConsultationBooking extends Component
{
    public int $step = 1;

    public ?int $consultantId = null;

    public ?int $typeId = null;

    public string $date = '';

    public ?int $slotId = null;

    public string $slotStart = '';

    public string $slotEnd = '';

    // مرحله ۳ — اطلاعات فردی
    public string $contactName = '';

    public string $contactPhone = '';

    public string $contactEmail = '';

    public string $notes = '';

    // مرحله ۴ — نتیجه
    public ?int $bookedConsultationId = null;

    public string $successMessage = '';

    protected function rules(): array
    {
        return [
            'contactName' => ['required', 'string', 'min:3', 'max:120'],
            'contactPhone' => ['required', 'string', 'regex:/^09\d{9}$/'],
            'contactEmail' => ['nullable', 'email', 'max:190'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected $validationAttributes = [
        'contactName' => 'نام',
        'contactPhone' => 'تلفن',
        'contactEmail' => 'ایمیل',
        'notes' => 'توضیحات',
    ];

    public function mount(): void
    {
        $user = auth()->user();

        if ($user) {
            $this->contactName = $user->name;
            $this->contactPhone = $user->phone ?? '';
            $this->contactEmail = $user->email ?? '';
        }

        // مشاور پیش‌فرض: اولین مشاور فعال — کاربر نیازی به انتخاب ندارد
        $consultant = Consultant::query()->where('is_active', true)->orderByDesc('rating')->first();
        $this->consultantId = $consultant?->id;
    }

    // ===== ناوبری مراحل =====

    public function chooseType(int $id): void
    {
        ConsultationType::query()->where('is_active', true)->findOrFail($id);
        $this->typeId = $id;
        $this->step = 2;
    }

    public function pickDate(string $date): void
    {
        $this->validateDate($date);

        $this->date = $date;
        $this->slotId = null;
        $this->slotStart = '';
        $this->slotEnd = '';
    }

    public function chooseSlot(int $id): void
    {
        $slot = $this->freeSlot($id);

        if (! $slot) {
            $this->addError('slot', 'این تایم همین حالا رزرو شد؛ لطفاً زمان دیگری انتخاب کنید.');
            $this->slotId = null;

            return;
        }

        $this->slotId = $slot['id'];
        $this->slotStart = $slot['start'];
        $this->slotEnd = $slot['end'];
    }

    public function nextStep(): void
    {
        if ($this->step === 2) {
            if (! $this->slotId || ! $this->date) {
                $this->addError('slot', 'لطفاً ابتدا یک تایم مشاوره انتخاب کنید.');

                return;
            }

            // بررسی مجدد آزاد بودن (ریل‌تایم)
            if (! $this->freeSlot($this->slotId)) {
                $this->addError('slot', 'تایم مشاوره خالی نیست؛ لطفاً زمان دیگری انتخاب کنید.');
                $this->slotId = null;

                return;
            }
        }

        if ($this->step === 3) {
            $this->validate();
        }

        $this->step = min($this->step + 1, 4);
    }

    public function prevStep(): void
    {
        $this->resetErrorBag();
        $this->step = max($this->step - 1, 1);
    }

    // ===== ثبت نهایی =====

    public function confirmBooking(): void
    {
        $this->validate();

        $user = auth()->user();
        $consultant = Consultant::findOrFail($this->consultantId);
        $type = ConsultationType::findOrFail($this->typeId);
        $slot = $this->freeSlotModel($this->slotId);

        if (! $slot) {
            $this->addError('slot', 'تایم مشاوره خالی نیست؛ لطفاً زمان دیگری انتخاب کنید.');
            $this->step = 2;

            return;
        }

        try {
            $consultation = app(ConsultationService::class)->book($user, $consultant, $type, $slot, [
                'name' => $this->contactName,
                'phone' => $this->contactPhone,
                'email' => $this->contactEmail,
                'notes' => $this->notes,
            ]);
        } catch (\RuntimeException $e) {
            $this->addError('slot', $e->getMessage());
            $this->step = 2;

            return;
        }

        $this->bookedConsultationId = $consultation->id;
        $this->successMessage = 'رزرو شما با موفقیت ثبت شد؛ پیامک اطلاع‌رسانی ارسال گردید.';
        $this->step = 4;
    }

    // ===== داده‌های نمایش =====

    public function render()
    {
        $types = ConsultationType::query()->where('is_active', true)->orderBy('price')->get();

        $calendar = [];
        $slots = [];

        if ($this->consultantId) {
            $consultant = Consultant::findOrFail($this->consultantId);
            $calendar = app(SlotService::class)->calendarAvailability($consultant);

            if ($this->date !== '') {
                $slots = app(SlotService::class)->availableSlots($consultant, $this->date);
            }
        }

        $consultant = $this->consultantId ? Consultant::with('user')->find($this->consultantId) : null;
        $type = $this->typeId ? $types->firstWhere('id', $this->typeId) : null;

        $lastBooking = $this->bookedConsultationId
            ? Consultation::with(['type', 'slot', 'consultant.user'])->find($this->bookedConsultationId)
            : null;

        return view('consultation::livewire.user-panel.consultation-booking', [
            'consultant' => $consultant,
            'types' => $types,
            'calendar' => $calendar,
            'slots' => $slots,
            'selectedType' => $type,
            'lastBooking' => $lastBooking,
        ]);
    }

    // ===== helpers =====

    private function validateDate(string $date): void
    {
        $parsed = \Carbon\CarbonImmutable::createFromFormat('Y-m-d', $date);

        if (! $parsed || $parsed->format('Y-m-d') !== $date) {
            $this->addError('date', 'تاریخ نامعتبر است.');

            return;
        }

        if ($parsed->lt(\Carbon\CarbonImmutable::today())) {
            $this->addError('date', 'نمی‌توان تاریخ گذشته را انتخاب کرد.');
        }
    }

    private function freeSlot(int $slotId): ?array
    {
        $consultant = Consultant::findOrFail($this->consultantId);
        $slots = app(SlotService::class)->availableSlots($consultant, $this->date);

        return collect($slots)->firstWhere('id', $slotId);
    }

    private function freeSlotModel(int $slotId): ?\Modules\Consultation\Models\ConsultationSlot
    {
        $consultant = Consultant::findOrFail($this->consultantId);
        $slots = app(SlotService::class)->availableSlots($consultant, $this->date);

        if (! collect($slots)->firstWhere('id', $slotId)) {
            return null;
        }

        return \Modules\Consultation\Models\ConsultationSlot::whereKey($slotId)->where('is_booked', false)->first();
    }
}
