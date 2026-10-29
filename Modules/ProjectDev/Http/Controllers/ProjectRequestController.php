<?php

namespace Modules\ProjectDev\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Modules\ProjectDev\Services\ProjectService;

/**
 * فرم ثبت درخواست پروژه — کاربر واردشده (مثل ثبت تیکت جدید)
 */
class ProjectRequestController extends Controller
{
    public function __construct(
        private readonly ProjectService $projects,
    ) {}

    /**
     * فرم درخواست پروژه (Livewire داخل صفحه)
     */
    public function create()
    {
        return view('projectdev::user.request-create');
    }

    /**
     * ذخیره درخواست — از طریق کامپوننت Livewire انجام می‌شود
     * (این متد فقط برای fallback فرم غیرجاوااسکریپتی)
     */
    public function store()
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = request()->validate([
            'title' => ['required', 'string', 'min:3', 'max:190'],
            'project_type_id' => ['required', 'integer', 'exists:project_types,id'],
            'description' => ['nullable', 'string', 'max:3000'],
            'option_ids' => ['nullable', 'array'],
            'option_ids.*' => ['integer'],
            'custom_options' => ['nullable', 'array'],
        ]);

        $request = $this->projects->createRequest($user, $validated);

        return redirect()
            ->route('user.dashboard', ['tab' => 'projects'])
            ->with('success', 'درخواست پروژه شما ثبت شد؛ به‌زودی بررسی و قیمت‌گذاری می‌شود.');
    }
}
