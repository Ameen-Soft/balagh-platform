<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryManagementController extends Controller
{
    /**
     * List all categories and their hierarchy.
     */
    public function index(): View
    {
        $categories = Category::with(['department.ministry', 'parent', 'complaints'])
            ->withCount('complaints')
            ->latest()
            ->paginate(20);

        $departments = Department::with('ministry')->get();
        $parentCategories = Category::whereNull('parent_id')->get();

        return view('admin.categories.index', compact('categories', 'departments', 'parentCategories'));
    }

    /**
     * Store a new category.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string|max:500',
        ]);

        $validated['level'] = !empty($validated['parent_id']) ? 2 : 1;

        Category::create($validated);

        return back()->with('success', 'تم إنشاء تصنيف البلاغات بنجاح.');
    }
}
