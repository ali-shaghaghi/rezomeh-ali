<?php

namespace Modules\Contact\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Contact\Services\ContactService;

class ContactController extends Controller
{
    public function __construct(
        private readonly ContactService $contactService,
    ) {}

    /**
     * دریافت پیام از ویجت شناور (POST /contact/message)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'email' => ['nullable', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:32'],
            'subject' => ['nullable', 'string', 'max:190'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $message = $this->contactService->sendMessage($validated, $request);

        return back()->with('success', 'پیام شما با موفقیت ارسال شد. به‌زودی پاسخ می‌دهیم.');
    }
}
