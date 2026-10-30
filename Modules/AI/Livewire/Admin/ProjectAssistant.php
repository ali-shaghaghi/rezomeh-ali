<?php

namespace Modules\AI\Livewire\Admin;

use Illuminate\Support\Str;
use Livewire\Component;
use Modules\AI\Services\AiService;
use Modules\ProjectDev\Models\ProjectRequest;

/**
 * ابزار ۲ — دستیار مدیریت پروژه‌ها.
 * تحلیل درخواست‌های پروژه + پیشنهاد برآورد قیمت/زمان/تکنولوژی با AI.
 */
class ProjectAssistant extends Component
{
    public string $analysis = '';

    public string $errorMessage = '';

    public bool $analyzing = false;

    public int $selectedRequestId = 0;

    public function analyzeLatest(): void
    {
        $this->analyzing = true;
        $this->errorMessage = '';

        try {
            $requests = ProjectRequest::with('user')
                ->where('status', 'pending')
                ->latest()
                ->take(5)
                ->get();

            if ($requests->isEmpty()) {
                $this->errorMessage = 'درخواست پروژه در انتظار بررسی وجود ندارد.';
                $this->analyzing = false;

                return;
            }

            $list = $requests->map(fn ($r) => "- [ID {$r->id}] {$r->title}: ".Str::limit($r->description ?? '', 200))->implode("\n");

            $system = 'تو دستیار مدیریت پروژه‌های نرم‌افزاری هستی. برای هر درخواست پروژه: ۱) سطح پیچیدگی (ساده/متوسط/پیچیده) ۲) تکنولوژی پیشنهادی ۳) برآورد زمان به روز کاری ۴) برآورد قیمت به تومان ۵) یک نکته مهم که ادمین باید قبل از قیمت‌گذاری بداند. خروجی مختصر و ساختاریافته به فارسی.';
            $user = "این درخواست‌های در انتظار بررسی هستند:\n{$list}\n\nبرای هرکدام تحلیل بده.";

            $this->analysis = app(AiService::class)->complete($system, $user);
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
        } finally {
            $this->analyzing = false;
        }
    }

    public function analyzeOne(int $requestId): void
    {
        $request = ProjectRequest::with('user')->find($requestId);
        if (! $request) {
            $this->errorMessage = 'درخواست یافت نشد.';

            return;
        }

        $this->analyzing = true;
        $this->errorMessage = '';
        $this->selectedRequestId = $requestId;

        try {
            $system = 'تو دستیار قیمت‌گذاری پروژه‌های نرم‌افزاری هستی. برای این پروژه: سطح پیچیدگی، تکنولوژی پیشنهادی، برآورد زمان (روز کاری)، برآورد قیمت (تومان) با توجه به نرخ‌های بازار ایران (سایت شرکتی از ۱۵ میلیون، CRM از ۵۰ میلیون)، ریسک‌های احتمالی و سوالاتی که باید از مشتری پرسیده شود. فارسی و ساختاریافته.';
            $user = "عنوان: {$request->title}\nنوع: {$request->project_type_name}\nتوضیحات: {$request->description}\nسختی اعلامی مشتری: {$request->difficulty_label}";

            $this->analysis = app(AiService::class)->complete($system, $user);
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
        } finally {
            $this->analyzing = false;
        }
    }

    public function render()
    {
        return view('ai::livewire.admin.project-assistant', [
            'pendingRequests' => ProjectRequest::with('user')
                ->where('status', 'pending')
                ->latest()
                ->take(8)
                ->get(),
        ]);
    }
}
