<?php

namespace Modules\Consultation\Http\Controllers;

use App\Http\Controllers\Controller;

class MyConsultationsController extends Controller
{
    /**
     * مشاوره‌های من — صفحه پنل کاربر
     */
    public function index()
    {
        return view('consultation::user.index');
    }

    /**
     * درخواست مشاوره جدید — ویزارد مرحله‌ای + جدول زمان‌بندی زنده
     */
    public function create()
    {
        return view('consultation::user.create');
    }
}