<?php

namespace Modules\SMS\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\SMS\Models\SmsLog;

/**
 * لاگ پیامک‌های ارسالی — فیلتر وضعیت/درایور + جستجو در شماره
 */
class SmsLogs extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public string $provider = '';

    /** شناسه لاگِ بازشده در مودال مشاهده کامل (null = بسته) */
    public ?int $viewLogId = null;

    /** باز کردن مودال مشاهده کامل لاگ — بدون لود صفحه */
    public function viewLog(int $id): void
    {
        abort_unless(SmsLog::whereKey($id)->exists(), 404);
        $this->viewLogId = $id;
    }

    /** بستن مودال */
    public function closeView(): void
    {
        $this->viewLogId = null;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingProvider(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $logs = SmsLog::query()
            ->with('user')
            ->when($this->search !== '', function ($q) {
                $q->where('phone', 'like', "%{$this->search}%")
                    ->orWhere('message', 'like', "%{$this->search}%");
            })
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when($this->provider !== '', fn ($q) => $q->where('provider', $this->provider))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('sms::livewire.admin.logs', [
            'logs' => $logs,
            'totalSent' => SmsLog::where('status', 'sent')->count(),
            'totalFailed' => SmsLog::where('status', 'failed')->count(),
            // هر بار رندر از دیتابیس خوانده می‌شود → با wire:poll مودال ریل‌تایم می‌ماند
            'viewLog' => $this->viewLogId
                ? SmsLog::with('user')->find($this->viewLogId)
                : null,
        ]);
    }
}