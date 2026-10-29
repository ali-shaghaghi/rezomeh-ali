<?php

namespace Modules\Resume\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Core\Models\Category;

class CategoryController extends Controller
{
    // دسته‌بندی‌های نمونه کار — زیرمنوی «نمونه کار» در بخش رزومه

    public function index()
    {
        $categories = Category::withCount('projects')->latest()->paginate(9);

        return view('resume::categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|alpha_dash|unique:categories,slug',
            'description' => 'nullable|string',
        ]);

        Category::create([
            'name' => $request->name,
            'slug' => $request->slug ?: Str::slug($request->name),
            'description' => $request->description,
        ]);

        return redirect()->route('admin.resume.portfolio.categories.index')
            ->with('success', 'دسته‌بندی با موفقیت ایجاد شد.');
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|alpha_dash|unique:categories,slug,'.$category->id,
            'description' => 'nullable|string',
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => $request->slug ?: Str::slug($request->name),
            'description' => $request->description,
        ]);

        return redirect()->route('admin.resume.portfolio.categories.index')
            ->with('success', 'دسته‌بندی با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('admin.resume.portfolio.categories.index')
            ->with('success', 'دسته‌بندی با موفقیت حذف شد.');
    }
}
