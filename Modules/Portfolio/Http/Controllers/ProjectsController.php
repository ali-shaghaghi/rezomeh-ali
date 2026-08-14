<?php

namespace Modules\Portfolio\Http\Controllers;

use Modules\Core\Models\Project;
use Modules\Core\Models\Category;
use Modules\Core\Models\Technology;
use Illuminate\Http\Request;
use Modules\Admin\Http\Controllers\Controller;

class ProjectsController extends Controller
{
    public function index()
    {
        $projects = Project::with(['category', 'technologies'])
            ->where('status', 'published')
            ->latest()
            ->paginate(10);

        return view('portfolio::projects.index', compact('projects'));
    }

    public function show(Project $project)
    {
        $project->load(['category', 'technologies']);
        return view('portfolio::projects.show', compact('project'));
    }

    public function create()
    {
        $categories = Category::all();
        $technologies = Technology::all();
        return view('portfolio::projects.create', compact('categories', 'technologies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|alpha_dash|unique:projects,slug',
            'description' => 'required|string',
            'thumbnail' => 'nullable|image|max:2048',
            'cover_image' => 'nullable|image|max:2048',
            'github_url' => 'nullable|url',
            'demo_url' => 'nullable|url',
            'video_url' => 'nullable|url',
            'status' => 'required|in:draft,published,archived',
            'featured' => 'nullable|boolean',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'category_ids' => 'nullable|array',
            'technology_ids' => 'nullable|array',
        ]);

        $project = Project::create([
            'title' => $request->title,
            'slug' => $request->slug,
            'description' => $request->description,
            'thumbnail' => $request->file('thumbnail') ? $request->file('thumbnail')->store('projects', 'public') : $request->thumbnail,
            'cover_image' => $request->file('cover_image') ? $request->file('cover_image')->store('projects', 'public') : $request->cover_image,
            'category_id' => $request->category_id,
            'github_url' => $request->github_url,
            'demo_url' => $request->demo_url,
            'video_url' => $request->video_url,
            'status' => $request->status,
            'featured' => $request->featured ?? false,
            'seo_title' => $request->seo_title,
            'seo_description' => $request->seo_description,
        ]);

        if ($request->category_ids) {
            $project->category()->sync($request->category_ids);
        }

        if ($request->technology_ids) {
            $project->technologies()->sync($request->technology_ids);
        }

        return redirect()->route('portfolio.projects.index')
            ->with('success', 'پروژه با موفقیت ایجاد شد.');
    }

    public function edit(Project $project)
    {
        $categories = Category::all();
        $technologies = Technology::all();
        $project->load(['category', 'technologies']);
        return view('portfolio::projects.edit', compact('project', 'categories', 'technologies'));
    }

    public function update(Request $request, Project $project)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|alpha_dash|unique:projects,slug,' . $project->id,
            'description' => 'required|string',
            'thumbnail' => 'nullable|image|max:2048',
            'cover_image' => 'nullable|image|max:2048',
            'github_url' => 'nullable|url',
            'demo_url' => 'nullable|url',
            'video_url' => 'nullable|url',
            'status' => 'required|in:draft,published,archived',
            'featured' => 'nullable|boolean',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'category_ids' => 'nullable|array',
            'technology_ids' => 'nullable|array',
        ]);

        $project->update([
            'title' => $request->title,
            'slug' => $request->slug,
            'description' => $request->description,
            'thumbnail' => $request->file('thumbnail') ? $request->file('thumbnail')->store('projects', 'public') : $project->thumbnail,
            'cover_image' => $request->file('cover_image') ? $request->file('cover_image')->store('projects', 'public') : $project->cover_image,
            'category_id' => $request->category_id,
            'github_url' => $request->github_url,
            'demo_url' => $request->demo_url,
            'video_url' => $request->video_url,
            'status' => $request->status,
            'featured' => $request->featured ?? $project->featured,
            'seo_title' => $request->seo_title,
            'seo_description' => $request->seo_description,
        ]);

        if ($request->category_ids) {
            $project->category()->sync($request->category_ids);
        }

        if ($request->technology_ids) {
            $project->technologies()->sync($request->technology_ids);
        }

        return redirect()->route('portfolio.projects.index')
            ->with('success', 'پروژه با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('portfolio.projects.index')
            ->with('success', 'پروژه با موفقیت حذف شد.');
    }
}