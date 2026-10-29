<?php

namespace Modules\ProjectDev\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\ProjectDev\Models\ProjectRequest;
use Modules\ProjectDev\Services\ProjectService;

/**
 * بررسی درخواست‌های پروژه — قیمت نهایی، سختی، زمان، ددلاین و تبدیل به سفارش
 */
class ProjectRequestsIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    // ---------- مودال بررسی ----------
    public bool $reviewModalOpen = false;

    public $reviewId = null;

    public array $reviewForm = [
        'final_price' => 0,
        'difficulty' => 'medium',
        'estimated_days' => 30,
        'deadline' => '',
        'admin_note' => '',
    ];

    public bool $reviewMode = false; // false=بررسی(تایید/رد) — true=تبدیل به سفارش

    // ---------- حذف ----------
    public bool $confirmingDelete = false;

    public $deleteId = null;

    // نوتیفیکیشن
    public bool $successToast = false;

    public string $successMessage = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'status']);
        $this->resetPage();
    }

    // ===================== بررسی =====================

    public function openReview(int $id, bool $convert = false): void
    {
        $this->resetErrorBag();
        $request = ProjectRequest::findOrFail($id);
        $this->authorize('update', $request);

        $this->reviewId = $id;
        $this->reviewMode = $convert;
        $this->reviewForm = [
            'final_price' => (float) ($request->final_price ?? $request->estimated_price),
            'difficulty' => $request->difficulty ?? 'medium',
            'estimated_days' => $request->estimated_days ?? 30,
            'deadline' => $request->deadline?->format('Y-m-d') ?? '',
            'admin_note' => (string) ($request->admin_note ?? ''),
        ];
        $this->reviewModalOpen = true;
    }

    public function saveReview(): void
    {
        $request = ProjectRequest::findOrFail($this->reviewId);
        $this->authorize('update', $request);

        $validated = $this->validate([
            'reviewForm.final_price' => ['required', 'numeric', 'min:0'],
            'reviewForm.difficulty' => ['required', 'in:low,medium,high'],
            'reviewForm.estimated_days' => ['required', 'integer', 'min:1', 'max:365'],
            'reviewForm.deadline' => ['nullable', 'date', 'after:today'],
            'reviewForm.admin_note' => ['nullable', 'string', 'max:2000'],
        ], [
            'reviewForm.final_price.required' => 'قیمت نهایی الزامی است.',
            'reviewForm.deadline.after' => 'ددلاین باید در آینده باشد.',
        ])['reviewForm'];

        $service = app(ProjectService::class);
        $request = $service->review($request, $validated + ['status' => 'approved']);

        if ($this->reviewMode) {
            $service->convertToOrder($request, ['amount' => $validated['final_price']]);
            $this->notifySuccess('درخواست به سفارش تبدیل شد — فاکتور با قسط‌های ۳۰/۳۰/۴۰ ساخته شد.');
        } else {
            $this->notifySuccess('درخواست تایید و قیمت‌گذاری شد.');
        }

        $this->reviewModalOpen = false;
        $this->reviewId = null;
    }

    public function reject(int $id): void
    {
        $request = ProjectRequest::findOrFail($id);
        $this->authorize('update', $request);

        app(ProjectService::class)->review($request, ['status' => 'rejected', 'final_price' => 0]);
        $this->notifySuccess('درخواست پروژه رد شد.');
    }

    public function confirmDelete($id): void
    {
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function deleteRequest(): void
    {
        $request = ProjectRequest::findOrFail($this->deleteId);
        $this->authorize('delete', $request);

        $request->delete();

        $this->confirmingDelete = false;
        $this->deleteId = null;
        $this->notifySuccess('درخواست پروژه حذف شد.');
    }

    private function notifySuccess(string $message): void
    {
        $this->successMessage = $message;
        $this->successToast = true;
    }

    public function render()
    {
        $service = app(ProjectService::class);

        return view('projectdev::livewire.admin.project-requests', [
            'requests' => $service->adminList([
                'search' => $this->search,
                'status' => $this->status,
            ]),
            'stats' => $service->adminStats(),
        ]);
    }
}
