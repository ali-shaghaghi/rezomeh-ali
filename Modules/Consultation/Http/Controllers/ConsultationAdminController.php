<?php

namespace Modules\Consultation\Http\Controllers;

use App\Http\Controllers\Controller;

class ConsultationAdminController extends Controller
{
    /**
     * دسترسی — قانون پروژه: غیرمجاز = 404
     */
    private function authorizeAccess(string $permission): void
    {
        abort_unless(auth()->user()?->hasPermission($permission), 404);
    }

    /**
     * لیست رزروها
     */
    public function index()
    {
        $this->authorizeAccess('manage_consultations');

        return view('consultation::admin.index');
    }

    /**
     * CRUD مشاوران
     */
    public function consultants()
    {
        $this->authorizeAccess('manage_consultants');

        return view('consultation::admin.consultants');
    }

    /**
     * زمان‌بندی مشاوران
     */
    public function schedules()
    {
        $this->authorizeAccess('manage_schedules');

        return view('consultation::admin.schedules');
    }
}