<?php

namespace Modules\Blog\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Core\Models\BlogPost;
use Modules\Core\Models\Category;

class BlogCategoryController extends Controller
{
    use AuthorizesRequests;

    // دسته‌بندی‌های وبلاگ (جدول مشترک categories با نمونه کار)

    public function index()
    {
        $this->authorize('viewAny', BlogPost::class);

        $categories = Category::withCount('blogPosts')->latest()->paginate(10);

        return view('blog::categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', BlogPost::class);

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

        return redirect()->route('admin.blog.categories.index')
            ->with('success', 'دسته‌بندی با موفقیت ایجاد شد.');
    }

    public function update(Request $request, Category $category)
    {
        $this->authorize('update', BlogPost::class);

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

        return redirect()->route('admin.blog.categories.index')
            ->with('success', 'دسته‌بندی با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(Category $category)
    {
        $this->authorize('delete', BlogPost::class);

        $category->delete();

        return redirect()->route('admin.blog.categories.index')
            ->with('success', 'دسته‌بندی با موفقیت حذف شد.');
    }
}
