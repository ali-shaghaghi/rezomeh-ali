<?php

namespace Modules\Analytics\Livewire\Admin;

use Livewire\Component;
use Modules\Analytics\Services\AnalyticsService;

/**
 * گزارش فروش — فیلتر بازه زمانی + روش پرداخت، جدول پرداخت‌های موفق
 * و لیست محصولات پرفروش.
 */
class SalesReport extends Component
{
    public ?string $fromDate = null;

    public ?string $toDate = null;

    public string $method = '';

    public function resetFilters(): void
    {
        $this->reset(['fromDate', 'toDate', 'method']);
    }

    public function render(AnalyticsService $analytics)
    {
        $payments = $analytics->payments($this->fromDate, $this->toDate, $this->method ?: null);

        return view('analytics::livewire.admin.sales', [
            'payments' => $payments,
            'totalAmount' => $payments->sum(fn ($p) => (float) $p->amount),
            'topProducts' => $analytics->topProducts(10),
            'methods' => AnalyticsService::PAYMENT_METHODS,
        ]);
    }
}
