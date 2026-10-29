<?php

namespace Modules\ProjectDev\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Orders\Models\Order;
use Modules\Orders\Services\OrderService;
use Modules\ProjectDev\Models\OrderInstallment;
use Modules\ProjectDev\Models\ProjectOption;
use Modules\ProjectDev\Models\ProjectRequest;
use Modules\ProjectDev\Models\ProjectType;

/**
 * سرویس ماژول ProjectDev — کاتالوگ قیمت‌گذاری + درخواست پروژه + قسط‌های 30/30/40
 *
 * اطلاع‌رسانی SMS در نقاط کلیدی (ثبت/پیشنهاد/تغییر وضعیت) از طریق Services Hub.
 */
class ProjectService
{
    public function __construct(
        private readonly \Modules\SMS\Services\SmsService $sms,
    ) {}

    /**
     * انواع پروژه فعال برای فرم کاربر
     */
    public function activeTypes()
    {
        return ProjectType::active()->ordered()->get();
    }

    /**
     * ویژگی‌های فعال برای یک نوع پروژه (ویژه آن نوع + عمومی)
     */
    public function activeOptionsForType(?int $typeId): array
    {
        return ProjectOption::active()
            ->where(function ($q) use ($typeId) {
                $q->whereNull('project_type_id')
                    ->orWhere('project_type_id', $typeId);
            })
            ->orderBy('project_type_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->all();
    }

    /**
     * محاسبه قیمت تخمینی: پایه نوع + جمع ویژگی‌های انتخابی
     */
    public function estimatePrice(?ProjectType $type, array $optionIds): float
    {
        $base = (float) ($type?->base_price ?? 0);
        $optionsTotal = ProjectOption::active()
            ->whereIn('id', array_map('intval', $optionIds))
            ->sum('price');

        return round($base + (float) $optionsTotal, 2);
    }

    /**
     * ثبت درخواست پروژه توسط کاربر
     */
    public function createRequest(User $user, array $data): ProjectRequest
    {
        $type = isset($data['project_type_id'])
            ? ProjectType::active()->find($data['project_type_id'])
            : null;

        $optionIds = array_values(array_filter(array_map('intval', (array) ($data['option_ids'] ?? []))));
        $options = empty($optionIds)
            ? collect()
            : ProjectOption::active()->whereIn('id', $optionIds)->orderBy('sort_order')->get();

        // اسنپ‌شات — تغییر قیمت کاتالوگ بعداً روی درخواست ثبت‌شده اثر نمی‌گذارد
        $custom = collect((array) ($data['custom_options'] ?? []))
            ->filter(fn ($c) => is_array($c) && trim((string) ($c['name'] ?? '')) !== '')
            ->map(fn ($c) => ['name' => trim((string) $c['name']), 'note' => trim((string) ($c['note'] ?? ''))])
            ->values()
            ->all();

        $estimated = $this->estimatePrice($type, $optionIds);

        $projectRequest = ProjectRequest::create([
            'user_id' => $user->id,
            'project_type_id' => $type?->id,
            'project_type_name' => $type?->name ?? (string) ($data['project_type_name'] ?? 'پروژه'),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'selected_options' => $options->map(fn ($o) => ['name' => $o->name, 'price' => (float) $o->price])->all(),
            'custom_options' => $custom,
            'features_snapshot' => [
                'base' => ['name' => $type?->name ?? $data['project_type_name'] ?? 'پروژه', 'price' => (float) ($type?->base_price ?? 0)],
                'options' => $options->map(fn ($o) => ['name' => $o->name, 'price' => (float) $o->price])->all(),
                'custom' => $custom,
            ],
            'estimated_price' => $estimated,
            'status' => 'pending',
        ]);

        // اطلاع‌رسانی SMS به کاربر — ثبت درخواست پروژه
        $this->notifySms($projectRequest, 'project_request_submitted', [
            'name' => $user->name,
            'title' => $projectRequest->title,
            'price' => number_format((float) $projectRequest->estimated_price),
        ]);

        return $projectRequest;
    }

    /**
     * درخواست‌های خود کاربر (پنل کاربر)
     */
    public function userRequests(User $user, int $perPage = 9)
    {
        return ProjectRequest::where('user_id', $user->id)
            ->with('order')
            ->latest()->latest('id')
            ->paginate($perPage);
    }

    /**
     * آمار پنل کاربر
     */
    public function userStats(User $user): array
    {
        $requests = ProjectRequest::where('user_id', $user->id);

        return [
            'projects_total' => (clone $requests)->count(),
            'projects_pending' => (clone $requests)->where('status', 'pending')->count(),
            'projects_approved' => (clone $requests)->whereIn('status', ['approved', 'converted'])->count(),
            'projects_rejected' => (clone $requests)->where('status', 'rejected')->count(),
        ];
    }

    /**
     * درخواست‌های ادمین — فیلتر/جستجو/صفحه‌بندی
     */
    public function adminList(array $filters = [], int $perPage = 9)
    {
        $query = ProjectRequest::with(['user', 'projectType', 'order']);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('project_type_name', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->latest('id')->paginate($perPage);
    }

    /**
     * آمار ادمین
     */
    public function adminStats(): array
    {
        return [
            'total' => ProjectRequest::count(),
            'pending' => ProjectRequest::where('status', 'pending')->count(),
            'approved' => ProjectRequest::where('status', 'approved')->count(),
            'converted' => ProjectRequest::where('status', 'converted')->count(),
        ];
    }

    /**
     * ثبت بررسی ادمین — قیمت نهایی/سختی/زمان/ددلاین/وضعیت
     */
    public function review(ProjectRequest $request, array $data): ProjectRequest
    {
        $oldStatus = $request->status;

        $request->fill([
            'final_price' => $data['final_price'] ?? null,
            'difficulty' => $data['difficulty'] ?? null,
            'estimated_days' => $data['estimated_days'] ?? null,
            'deadline' => $data['deadline'] ?? null,
            'admin_note' => $data['admin_note'] ?? null,
            'status' => $data['status'] ?? $request->status,
            'reviewed_at' => now(),
        ]);
        $request->save();

        $request->refresh();

        $newStatus = $request->status;

        // SMS پیشنهاد قیمت — وقتی ادمین قیمت نهایی را ثبت و تایید می‌کند
        if ($oldStatus !== 'approved' && $newStatus === 'approved') {
            $this->notifySms($request, 'project_proposal_sent', [
                'name' => $request->user?->name ?? 'کاربر',
                'title' => $request->title,
                'price' => number_format((float) ($request->final_price ?? $request->estimated_price)),
                'days' => (string) ($request->estimated_days ?? '—'),
            ]);
        }

        // SMS تغییر وضعیت — برای هر تغییر وضعیت به‌جز تایید اولیه (بالای)
        if ($oldStatus !== $newStatus && $newStatus !== 'approved') {
            $this->notifySms($request, 'project_status_changed', [
                'title' => $request->title,
                'status' => $request->status_label,
            ]);
        }

        return $request;
    }

    /**
     * تبدیل درخواست تاییدشده به سفارش (فاکتور نهایی) + ساخت قسط‌های 30/30/40
     */
    public function convertToOrder(ProjectRequest $request, array $data): Order
    {
        return DB::transaction(function () use ($request, $data) {
            $amount = (float) ($data['amount'] ?? $request->final_price ?? $request->estimated_price);

            $order = app(OrderService::class)->save(new Order, [
                'user_id' => $request->user_id,
                'title' => $data['title'] ?? $request->title,
                'description' => $data['description'] ?? $request->description,
                'status' => 'accepted',
                'amount' => $amount,
                'priority' => $data['priority'] ?? 'medium',
                'deadline' => $data['deadline'] ?? $request->deadline,
                'start_date' => now()->toDateString(),
            ]);

            // قسط‌بندی 30/30/40 بر اساس قیمت نهایی و زمان تحویل
            $this->generateInstallments($order, $amount, $request->deadline);

            $request->update([
                'status' => 'converted',
                'final_price' => $amount,
                'order_id' => $order->id,
            ]);

            // SMS تغییر وضعیت — تبدیل به سفارش/فاکتور نهایی
            $this->notifySms($request, 'project_status_changed', [
                'title' => $request->title,
                'status' => 'تبدیل به سفارش',
            ]);

            return $order;
        });
    }

    /**
     * ساخت قسط‌های 30/30/40 — ددلاین‌ها از ابتدا تا تحویل توزیع می‌شوند
     */
    public function generateInstallments(Order $order, float $amount, $deadline = null): void
    {
        $order->installments()->delete();

        $plan = [
            1 => ['title' => 'پیش‌پرداخت (۳۰٪)', 'percent' => 30],
            2 => ['title' => 'نیمه کار (۳۰٪)', 'percent' => 30],
            3 => ['title' => 'تحویل پروژه (۴۰٪)', 'percent' => 40],
        ];

        $days = $deadline ? max(0, (int) now()->startOfDay()->diffInDays($deadline, false)) : null;

        foreach ($plan as $number => $item) {
            OrderInstallment::create([
                'order_id' => $order->id,
                'installment_number' => $number,
                'title' => $item['title'],
                'amount' => round($amount * $item['percent'] / 100, 2),
                'percent' => $item['percent'],
                'due_date' => $this->installmentDueDate($days, $number),
                'status' => 'unpaid',
            ]);
        }
    }

    private function installmentDueDate(?int $days, int $number): ?string
    {
        if ($days === null || $days <= 0) {
            return null;
        }

        // قسط اول همان ابتدا؛ دوم وسط مسیر؛ سوم زمان تحویل
        $offset = match ($number) {
            1 => 0,
            2 => (int) floor($days / 2),
            default => $days,
        };

        return now()->startOfDay()->addDays($offset)->toDateString();
    }

    /**
     * ارسال پیامک اطلاع‌رسانی به کاربر صاحب درخواست.
     *
     * ponytail: خطاها بلعیده و فقط لاگ می‌شوند — نباید جریان اصلی
     * (ثبت/بررسی/تبدیل) به‌خاطر قطع بودن سرویس پیامک بشکند.
     */
    private function notifySms(ProjectRequest $request, string $templateKey, array $vars = []): void
    {
        $phone = $request->user?->phone;

        if (! $phone) {
            return;
        }

        try {
            $this->sms->send($phone, $templateKey, $vars, $request->user);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('ProjectDev SMS failed', [
                'template' => $templateKey,
                'request_id' => $request->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
