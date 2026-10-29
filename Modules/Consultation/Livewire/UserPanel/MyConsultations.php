<?php

namespace Modules\Consultation\Livewire\UserPanel;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Consultation\Models\Consultation;

/**
 * رزروهای مشاوره من — در پنل کاربر
 */
class MyConsultations extends Component
{
    use WithPagination;

    public string $status = '';

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    /**
     * لغو رزرو توسط خود کاربر (فقط pending/confirmed)
     */
    public function cancel(int $id): void
    {
        $consultation = Consultation::where('user_id', auth()->id())->findOrFail($id);

        if (! in_array($consultation->status, ['pending', 'confirmed'], true)) {
            return;
        }

        app(\Modules\Consultation\Services\ConsultationService::class)->setStatus(
            $consultation,
            \Modules\Consultation\Enums\ConsultationStatus::Cancelled
        );

        session()->flash('success', 'رزرو شما لغو شد و تایم آزاد گردید.');
    }

    public function render()
    {
        $consultations = Consultation::query()
            ->with(['consultant.user', 'type', 'slot'])
            ->where('user_id', auth()->id())
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('consultation::livewire.user-panel.my-consultations', [
            'consultations' => $consultations,
        ]);
    }
}