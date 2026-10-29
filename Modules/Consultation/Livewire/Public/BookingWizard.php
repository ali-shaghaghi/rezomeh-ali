<?php

namespace Modules\Consultation\Livewire\Public;

use Carbon\CarbonImmutable;
use Livewire\Component;
use Modules\Consultation\Models\Consultant;
use Modules\Consultation\Models\Consultation;
use Modules\Consultation\Models\ConsultationType;
use Modules\Consultation\Services\ConsultationService;
use Modules\Consultation\Services\SlotService;
use Modules\Settings\Models\Setting;
use Modules\Orders\Services\PaymentService;

/**
 * ویزارد رزرو مشاوره — ۶ مرحله بدون رفرش صفحه:
 * مشاور ← نوع ← تاریخ و اسلات ← اطلاعات فردی ← پرداخت ← تایید
 */
class BookingWizard extends Component
{
    public int $step = 1;

    // انتخاب‌های ویزارد
    public ?int $consultantId = null;

    public ?int $typeId = null;

    public string $date = '';

    public ?int $slotId = null;

    public string $slotStart = '';

    public string $slotEnd = '';

    // مرحله ۴ — اطلاعات فردی
    public string $contactName = '';

    public string $contactPhone = '';

    public string $contactEmail = '';

    public string $notes = '';

    // مرحله ۶ — نتیجه
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

    public function validationAttributes(): array
    {
        return [
            'contactName' => __('consultation.name'),
            'contactPhone' => __('consultation.phone'),
            'contactEmail' => __('consultation.email_optional'),
            'notes' => __('consultation.notes_optional'),
        ];
    }

    public function mount(): void
    {
        $user = auth()->user();

        if ($user) {
            $this->contactName = $user->name;
            $this->contactPhone = $user->phone ?? '';
            $this->contactEmail = $user->email ?? '';
        }
    }

    // ===== ناوبری مراحل =====

    public function chooseConsultant(int $id): void
    {
        // رزرو مشاوره فقط برای کاربران واردشده — قبل از شروع ویزارد به صفحه ورود هدایت می‌شود
        // و بعد از ورود، کاربر به همین صفحه برمی‌گردد (url.intended)
        if (! auth()->check()) {
            session(['url.intended' => url()->full()]);
            $this->redirect(route('auth.login'));

            return;
        }

        Consultant::query()->where('is_active', true)->findOrFail($id);
        $this->consultantId = $id;
        $this->typeId = null;
        $this->date = '';
        $this->slotId = null;
        $this->step = 2;
    }

    public function chooseType(int $id): void
    {
        ConsultationType::query()->where('is_active', true)->findOrFail($id);
        $this->typeId = $id;
        $this->step = 3;
    }

    public function pickDate(string $date): void
    {
        $this->validateDate($date);

        $this->date = $date;
        $this->slotId = null;
    }

    public function chooseSlot(int $id): void
    {
        $slot = $this->freeSlot($id);

        if (! $slot) {
            $this->addError('slot', __('consultation.slot_taken'));
            $this->slotId = null;

            return;
        }

        $this->slotId = $slot['id'];
        $this->slotStart = $slot['start'];
        $this->slotEnd = $slot['end'];
    }

    public function nextStep(): void
    {
        if ($this->step === 3) {
            if (! $this->slotId || ! $this->date) {
                $this->addError('slot', __('consultation.pick_slot_first'));

                return;
            }

            // دوباره آزاد بودن چک می‌شود (ری‌آل‌تایم)
            if (! $this->freeSlot($this->slotId)) {
                $this->addError('slot', __('consultation.no_slots_error'));
                $this->slotId = null;

                return;
            }
        }

        if ($this->step === 4) {
            $this->validate();
        }

        $this->step = min($this->step + 1, 6);
    }

    public function prevStep(): void
    {
        $this->resetErrorBag();
        $this->step = max($this->step - 1, 1);
    }

    // ===== مرحله ۵ — پرداخت و ثبت نهایی =====

    public function confirmBooking(): void
    {
        $this->validate();

        $user = auth()->user();

        // محافظ: بدون ورود امکان رزرو نیست (مسیر مستقیم/نشست منقضی)
        if (! $user) {
            session(['url.intended' => url()->full()]);
            $this->redirect(route('auth.login'));

            return;
        }

        $consultant = Consultant::findOrFail($this->consultantId);
        $type = ConsultationType::findOrFail($this->typeId);
        $slot = $this->freeSlotModel($this->slotId);

        if (! $slot) {
            $this->addError('slot', __('consultation.no_slots_error'));
            $this->step = 3;

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
            $this->step = 3;

            return;
        }

        $this->bookedConsultationId = $consultation->id;

        // ===== ارجاع به درگاه پرداخت =====
        $payment = $consultation->order?->payments()?->latest('id')->first();
        $paymentService = app(PaymentService::class);

        if ($payment && $payment->status === 'pending' && $paymentService->isOnlineEnabled()) {
            // صفحه درگاه: زرین‌پال → هدایت مستقیم به StartPay؛ سایر درایورها → صفحه درگاه
            $this->redirect(route('payment.gateway', ['payment' => $payment->id]));

            return;
        }

        // پرداخت دستی (کارت به کارت) → ثبت موفق و انتظار تأیید ادمین
        $this->successMessage = __('consultation.booking_success');
        $this->step = 6;
    }

    // ===== داده‌های نمایش =====

    public function render()
    {
        $consultants = Consultant::query()->with(['user', 'admin'])->where('is_active', true)->orderByDesc('rating')->get();
        $types = ConsultationType::query()->where('is_active', true)->orderBy('price')->get();

        $calendar = [];
        $slots = [];

        if ($this->consultantId && $this->step >= 3) {
            $consultant = Consultant::findOrFail($this->consultantId);
            $calendar = app(SlotService::class)->calendarAvailability($consultant);

            if ($this->date !== '') {
                $slots = app(SlotService::class)->availableSlots($consultant, $this->date);
            }
        }

        $type = $this->typeId ? $types->firstWhere('id', $this->typeId) : null;
        $consultant = $this->consultantId ? $consultants->firstWhere('id', $this->consultantId) : null;

        $lastBooking = $this->bookedConsultationId
            ? Consultation::with(['type', 'slot', 'consultant.user'])->find($this->bookedConsultationId)
            : null;

        return view('consultation::livewire.public.booking-wizard', [
            'consultants' => $consultants,
            'types' => $types,
            'calendar' => $calendar,
            'slots' => $slots,
            'selectedType' => $type,
            'selectedConsultant' => $consultant,
            'lastBooking' => $lastBooking,
            'adminPhone' => Setting::get('contact_phone', ''),
        ]);
    }

    // ===== helpers =====

    private function validateDate(string $date): void
    {
        $parsed = CarbonImmutable::createFromFormat('Y-m-d', $date);

        if (! $parsed || $parsed->format('Y-m-d') !== $date) {
            $this->addError('date', __('consultation.invalid_date'));

            return;
        }

        if ($parsed->lt(CarbonImmutable::today())) {
            $this->addError('date', __('consultation.past_date'));
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