<?php

namespace Modules\Orders\Livewire\UserPanel;

use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Orders\Services\UserOrderService;

/**
 * سفارش‌های من — لیست زنده با جستجو و فیلتر وضعیت (پنل کاربر، ماژول Orders)
 */
class MyOrders extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

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

    public function render()
    {
        $user = auth()->user();

        return view('orders::livewire.user-panel.my-orders', [
            'orders' => app(UserOrderService::class)->orders(
                $user,
                config('userpanel.orders_per_page', 9),
                trim($this->search) !== '' ? trim($this->search) : null,
                $this->status !== '' ? $this->status : null
            ),
        ]);
    }
}
