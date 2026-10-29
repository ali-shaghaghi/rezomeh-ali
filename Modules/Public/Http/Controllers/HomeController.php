<?php

namespace Modules\Public\Http\Controllers;

use Modules\Consultation\Models\ConsultationType;
use Modules\ProjectDev\Models\ProjectRequest;
use Modules\ProjectDev\Models\ProjectType;
use Modules\Products\Services\ProductService;
use Modules\Public\Services\PublicService;

class HomeController extends \App\Http\Controllers\Controller
{
    public function __construct(
        private readonly PublicService $publicService,
        private readonly ProductService $productService,
    ) {}

    /**
     * تک‌پیج اصلی — رزومه + نمونه‌کارها + محصولات + مشاوره + مقالات
     */
    public function index()
    {
        $profile = $this->publicService->profile();
        $skills = $this->publicService->skills();
        $experiences = $this->publicService->experiences();
        $projects = $this->publicService->projects(config('public.home_projects_limit', 6));
        $products = $this->productService->resumeProducts(config('public.home_products_limit', 6));
        $posts = $this->publicService->posts(config('public.home_posts_limit', 3));

        // بنر «انجام پروژه» — تعداد پروژه‌های تحویل‌شده + انواع پروژه
        $deliveredProjects = ProjectRequest::where('status', 'converted')->count() ?: 0;
        $projectTypes = ProjectType::orderBy('sort_order')->orderBy('name')->limit(6)->get();

        // بخش مشاوره تخصصی — انواع مشاوره فعال
        $consultationTypes = ConsultationType::where('is_active', true)->orderBy('id')->limit(4)->get();

        return view('public::home', compact(
            'profile',
            'skills',
            'experiences',
            'projects',
            'products',
            'posts',
            'deliveredProjects',
            'projectTypes',
            'consultationTypes'
        ));
    }
}
