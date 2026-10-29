<?php

namespace Modules\ProjectDev\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Modules\ProjectDev\Models\ProjectRequest;
use Modules\ProjectDev\Models\ProjectType;

class ProjectAdminController extends Controller
{
    use AuthorizesRequests;

    /**
     * کاتالوگ قیمت‌گذاری — انواع پروژه + ویژگی‌ها (Livewire)
     */
    public function types()
    {
        $this->authorize('viewAny', ProjectType::class);

        return view('projectdev::admin.types');
    }

    /**
     * درخواست‌های پروژه — لیست + بررسی با مودال (Livewire)
     */
    public function requests()
    {
        $this->authorize('viewAny', ProjectRequest::class);

        return view('projectdev::admin.requests');
    }
}
