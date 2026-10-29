<?php

namespace Modules\Orders\Providers;

use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Modules\Core\Providers\BaseServiceProvider;
use Modules\Orders\Livewire\Admin\OrderShow;
use Modules\Orders\Livewire\Admin\OrdersIndex;
use Modules\Orders\Models\Order;
use Modules\Orders\Policies\OrderPolicy;

class OrdersServiceProvider extends BaseServiceProvider
{
    protected function moduleName(): string
    {
        return 'Orders';
    }

    public function boot(): void
    {
        parent::boot();

        // دیتابیس ماژول سفارشات — خودکفا (مایگریشن‌های orders/payments داخل ماژول)
        $this->loadMigrationsFrom(module_path('Orders', 'Database'.DIRECTORY_SEPARATOR.'Migrations'));

        // روتینگ ماژول سفارشات — خودکفا (ادمین با پیشوند admin/orders)
        $this->loadRoutesFrom(module_route_path('Orders', 'admin.php'));

        // روتینگ پرداخت مشترک — درگاه + callback عمومی (Products/Consultation/ProjectDev)
        $this->loadRoutesFrom(module_route_path('Orders', 'web.php'));

        // روتینگ پنل کاربر — جزئیات سفارش زیر /dashboard + ریدایرکت‌های 301 قدیمی
        $this->loadRoutesFrom(module_route_path('Orders', 'userpanel.php'));

        // ثبت Policies (مدل‌های ماژول با کشف خودکار لاراول پیدا نمی‌شوند)
        Gate::policy(Order::class, OrderPolicy::class);

        // ثبت کامپوننت‌های Livewire
        Livewire::component('admin.orders.index', OrdersIndex::class);
        Livewire::component('admin.orders.show', OrderShow::class);

        // پنل کاربر — سفارش‌های من (شل ریل‌تایم پنل، این کامپوننت‌ها را از ماژول Orders فراخوانی می‌کند)
        Livewire::component('orders.my-orders', \Modules\Orders\Livewire\UserPanel\MyOrders::class);
        Livewire::component('orders.order-details', \Modules\Orders\Livewire\UserPanel\OrderDetails::class);
    }

    public function register(): void
    {
        parent::register();
    }
}
