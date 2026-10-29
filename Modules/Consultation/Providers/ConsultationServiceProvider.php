<?php

namespace Modules\Consultation\Providers;

use Livewire\Livewire;
use Modules\Core\Providers\BaseServiceProvider;

class ConsultationServiceProvider extends BaseServiceProvider
{
    protected function moduleName(): string
    {
        return 'Consultation';
    }

    public function boot(): void
    {
        parent::boot();

        // دیتابیس ماژول مشاوره — خودکفا (consultants/types/schedules/slots/consultations/notes)
        $this->loadMigrationsFrom(module_path('Consultation', 'Database'.DIRECTORY_SEPARATOR.'Migrations'));

        // روتینگ عمومی (/consultation)، ادمین (/admin/consultations) و پنل کاربر (/dashboard/consultations)
        $this->loadRoutesFrom(module_route_path('Consultation', 'web.php'));
        $this->loadRoutesFrom(module_route_path('Consultation', 'admin.php'));
        $this->loadRoutesFrom(module_route_path('Consultation', 'userpanel.php'));

        // کامپوننت‌های Livewire
        Livewire::component('consultation.booking-wizard', \Modules\Consultation\Livewire\Public\BookingWizard::class);
        Livewire::component('consultation.admin.consultants-manager', \Modules\Consultation\Livewire\Admin\ConsultantsManager::class);
        Livewire::component('consultation.admin.schedules-manager', \Modules\Consultation\Livewire\Admin\SchedulesManager::class);
        Livewire::component('consultation.admin.consultations-index', \Modules\Consultation\Livewire\Admin\ConsultationsIndex::class);
        Livewire::component('consultation.my-consultations', \Modules\Consultation\Livewire\UserPanel\MyConsultations::class);
        Livewire::component('consultation.consultation-booking', \Modules\Consultation\Livewire\UserPanel\ConsultationBooking::class);
    }

    public function register(): void
    {
        parent::register();
    }
}