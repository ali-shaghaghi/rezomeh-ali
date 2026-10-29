<?php

namespace Modules\Consultation\Http\Controllers;

use App\Http\Controllers\Controller;

class ConsultationPublicController extends Controller
{
    /**
     * صفحه مشاوره — لیست مشاوران + ویزارد رزرو
     */
    public function index()
    {
        return view('consultation::public.index');
    }

    /**
     * رزرو مشاوره (کاربر واردشده)
     */
    public function book()
    {
        return view('consultation::public.index', [
            'autoStartWizard' => true,
        ]);
    }
}