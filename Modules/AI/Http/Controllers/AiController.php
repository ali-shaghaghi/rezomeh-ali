<?php

namespace Modules\AI\Http\Controllers;

use App\Http\Controllers\Controller;

class AiController extends Controller
{
    /**
     * مرکز دستیار هوشمند — سه ابزار در یک صفحه
     */
    public function index()
    {
        return view('ai::admin.index');
    }
}
