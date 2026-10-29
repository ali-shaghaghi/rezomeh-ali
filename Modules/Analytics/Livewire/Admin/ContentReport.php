<?php

namespace Modules\Analytics\Livewire\Admin;

use Livewire\Component;
use Modules\Analytics\Services\AnalyticsService;

/**
 * گزارش محتوا — مقالات پربازدید، آمار فروشگاه محصولات و مشاوره‌ها.
 */
class ContentReport extends Component
{
    public function render(AnalyticsService $analytics)
    {
        return view('analytics::livewire.admin.content', [
            'topPosts' => $analytics->topPosts(10),
            'productStats' => $analytics->productStats(),
            'consultationStats' => $analytics->consultationStats(),
        ]);
    }
}
