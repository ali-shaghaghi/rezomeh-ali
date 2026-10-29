<?php

namespace Modules\Consultation\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Consultation\Enums\ConsultationStatus;
use Modules\Consultation\Models\Consultation;
use Modules\Consultation\Services\ConsultationService;

/**
 * لیست رزروهای مشاوره — فیلتر وضعیت + تایید/لغو + تغییر وضعیت
 */
class ConsultationsIndex extends Component
{
    use WithPagination;

    public string $status = '';

    public string $search = '';

    /** پیام عملیات (موفق/خطا) — داخل همین کامپوننت نمایش داده می‌شود */
    public string $notice = '';

    public string $noticeType = 'success';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    /**
     * تایید رزرو
     */
    public function confirm(int $id): void
    {
        $this->applyStatus($id, ConsultationStatus::Confirmed, 'رزرو تایید شد.');
    }

    /**
     * لغو رزرو — اسلات آزاد می‌شود
     */
    public function cancel(int $id): void
    {
        $this->applyStatus($id, ConsultationStatus::Cancelled, 'رزرو لغو شد و تایم آزاد گردید.');
    }

    /**
     * تغییر وضعیت عمومی
     */
    public function setStatus(int $id, string $status): void
    {
        if (! in_array($status, array_column(ConsultationStatus::cases(), 'value'), true)) {
            return;
        }

        $this->applyStatus($id, ConsultationStatus::from($status), 'وضعیت به‌روزرسانی شد.');
    }

    /**
     * اعمال وضعیت + مدیریت خطای برگشت‌ناپذیری «عدم حضور»
     */
    private function applyStatus(int $id, ConsultationStatus $status, string $successMessage): void
    {
        try {
            app(ConsultationService::class)->setStatus(Consultation::findOrFail($id), $status);
        } catch (\RuntimeException $e) {
            $this->notice = $e->getMessage();
            $this->noticeType = 'error';
            session()->flash('error', $e->getMessage());

            return;
        }

        $this->notifySuccess($successMessage);
    }

    public function render()
    {
        $consultations = Consultation::query()
            ->with(['user', 'consultant.user', 'consultant.admin', 'type', 'slot', 'order'])
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when($this->search !== '', function ($q) {
                $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%"))
                    ->orWhereHas('consultant.user', fn ($c) => $c->where('name', 'like', "%{$this->search}%"))
                    ->orWhereHas('consultant.admin', fn ($c) => $c->where('name', 'like', "%{$this->search}%"));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('consultation::livewire.admin.consultations-index', [
            'consultations' => $consultations,
            'statuses' => collect(ConsultationStatus::cases())->mapWithKeys(
                fn (ConsultationStatus $s) => [$s->value => $s->label()]
            ),
        ]);
    }

    private function notifySuccess(string $message): void
    {
        $this->notice = $message;
        $this->noticeType = 'success';

        // نوتیفیشن ساده سشن-محور برای صفحه بلید والد
        session()->flash('success', $message);
    }
}