<?php

namespace Modules\Contact\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ChatController extends Controller
{
    /**
     * صفحه مدیریت چت آنلاین (#۲۱)
     *Livewire ChatManager گفتگوها را رندر می‌کند.
     */
    public function index()
    {
        return view('contact::admin.chat.index');
    }
}
