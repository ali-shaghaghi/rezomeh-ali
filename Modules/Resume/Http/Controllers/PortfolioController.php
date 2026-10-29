<?php

namespace Modules\Resume\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Modules\Resume\Models\Project;

class PortfolioController extends Controller
{
    use AuthorizesRequests;

    /**
     * نمایش لیست نمونه کارها (Livewire Index)
     */
    public function index()
    {
        $this->authorize('viewAny', Project::class);

        return view('resume::portfolio.index');
    }

    /**
     * فرم ایجاد نمونه کار جدید (Livewire Form)
     */
    public function create()
    {
        $this->authorize('create', Project::class);

        return view('resume::portfolio.create');
    }

    /**
     * نمایش مستقل نمونه کار (Livewire Show)
     */
    public function show(Project $project)
    {
        return view('resume::portfolio.show', compact('project'));
    }

    /**
     * فرم ویرایش نمونه کار (Livewire Form)
     */
    public function edit(Project $project)
    {
        return view('resume::portfolio.edit', compact('project'));
    }
}
