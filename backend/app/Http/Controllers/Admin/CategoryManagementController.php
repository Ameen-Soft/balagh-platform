<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Department;
use App\Models\Ministry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CategoryManagementController extends Controller
{
    /**
     * List all categories and their hierarchy.
     */
    public function index(): View
    {
        $mainCategories = Category::whereNull('parent_id')
            ->with(['ministry', 'children.department', 'children' => fn ($q) => $q->withCount('complaints')])
            ->withCount('complaints')
            ->orderBy('name')
            ->get();

        $categories = Category::with(['ministry', 'department.ministry', 'parent'])
            ->withCount('complaints')
            ->latest()
            ->paginate(25);

        $ministries = Ministry::where('is_active', true)->orderBy('name')->get();
        $departments = Department::with('ministry')->where('is_active', true)->orderBy('name')->get();
        $parentCategories = Category::whereNull('parent_id')->orderBy('name')->get();

        return view('admin.categories.index', compact('categories', 'mainCategories', 'ministries', 'departments', 'parentCategories'));
    }

    /**
     * Store a new main category or subcategory.
     */
    public function store(Request $request): RedirectResponse
    {
        $isSubcategory = $request->filled('parent_id');

        if (! $isSubcategory) {
            // Main Category (Level 1)
            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('categories', 'name')
                        ->where('ministry_id', $request->input('ministry_id'))
                        ->whereNull('parent_id'),
                ],
                'ministry_id' => 'required|integer|exists:ministries,id',
                'description' => 'nullable|string|max:500',
            ], [
                'name.required' => 'اسم التصنيف الرئيسي مطلوب.',
                'name.unique' => 'يوجد تصنيف رئيسي مسجل بهذا الاسم بالفعل تحت هذه الوزارة.',
                'ministry_id.required' => 'يرجى تحديد الوزارة التابع لها التصنيف.',
                'ministry_id.exists' => 'الوزارة المحددة غير صالحة.',
            ]);

            Category::create([
                'name' => $validated['name'],
                'ministry_id' => $validated['ministry_id'],
                'department_id' => null,
                'parent_id' => null,
                'description' => $validated['description'] ?? '',
                'level' => 1,
                'is_active' => true,
            ]);

            return back()->with('success', 'تم إنشاء التصنيف الرئيسي للوزارة بنجاح.');
        }

        // Subcategory (Level 2)
        $parent = Category::with('ministry')->findOrFail($request->input('parent_id'));
        if ($parent->level !== 1 || ! empty($parent->parent_id)) {
            throw ValidationException::withMessages([
                'parent_id' => ['لا يمكن ربط التصنيف الفرعي إلا بتصنيف رئيسي (مستوى أول).'],
            ]);
        }

        $department = Department::findOrFail($request->input('department_id'));

        // Verify department belongs to the same ministry as the parent category
        if ($department->ministry_id !== $parent->ministry_id) {
            throw ValidationException::withMessages([
                'department_id' => ['الإدارة الخدمية المحددة تتبع وزارة مختلفة عن وزارة التصنيف الرئيسي.'],
            ]);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')
                    ->where('parent_id', $parent->id),
            ],
            'department_id' => 'required|integer|exists:departments,id',
            'parent_id' => 'required|integer|exists:categories,id',
            'description' => 'nullable|string|max:500',
        ], [
            'name.required' => 'اسم نوع المشكلة (التصنيف الفرعي) مطلوب.',
            'name.unique' => 'هذا التصنيف الفرعي مسجل مسبقاً تحت نفس التصنيف الرئيسي.',
            'department_id.required' => 'يرجى اختيار الإدارة الخدمية المسؤولة.',
        ]);

        Category::create([
            'name' => $validated['name'],
            'ministry_id' => $parent->ministry_id,
            'department_id' => $department->id,
            'parent_id' => $parent->id,
            'description' => $validated['description'] ?? '',
            'level' => 2,
            'is_active' => true,
        ]);

        return back()->with('success', 'تم إنشاء التصنيف الفرعي وربطه بالإدارة المسؤولة بنجاح.');
    }

    /**
     * Update an existing category.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        if ($category->level === 1 || empty($category->parent_id)) {
            // Update Main Category
            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('categories', 'name')
                        ->where('ministry_id', $category->ministry_id)
                        ->whereNull('parent_id')
                        ->ignore($category->id),
                ],
                'description' => 'nullable|string|max:500',
            ], [
                'name.required' => 'اسم التصنيف الرئيسي مطلوب.',
                'name.unique' => 'يوجد تصنيف رئيسي مسجل بهذا الاسم بالفعل تحت هذه الوزارة.',
            ]);

            $category->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? '',
            ]);

            return back()->with('success', 'تم تحديث بيانات التصنيف الرئيسي بنجاح.');
        }

        // Update Subcategory
        $department = Department::findOrFail($request->input('department_id'));
        if ($department->ministry_id !== $category->ministry_id) {
            throw ValidationException::withMessages([
                'department_id' => ['الإدارة الخدمية المحددة تتبع وزارة مختلفة عن وزارة التصنيف.'],
            ]);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')
                    ->where('parent_id', $category->parent_id)
                    ->ignore($category->id),
            ],
            'department_id' => 'required|integer|exists:departments,id',
            'description' => 'nullable|string|max:500',
        ], [
            'name.required' => 'اسم التصنيف الفرعي مطلوب.',
            'name.unique' => 'هذا التصنيف الفرعي مسجل مسبقاً تحت نفس التصنيف الرئيسي.',
            'department_id.required' => 'يرجى اختيار الإدارة الخدمية المسؤولة.',
        ]);

        $category->update([
            'name' => $validated['name'],
            'department_id' => $department->id,
            'description' => $validated['description'] ?? '',
        ]);

        return back()->with('success', 'تم تحديث التصنيف الفرعي بنجاح (يسري تغيير الإدارة على البلاغات الجديدة فقط).');
    }

    /**
     * Toggle category active state (archive/activate).
     */
    public function toggleActive(Category $category): RedirectResponse
    {
        $newState = ! $category->is_active;
        $category->update(['is_active' => $newState]);

        $statusText = $newState ? 'تفعيل' : 'تعطيل وأرشفة';
        $notice = $category->level === 1 && ! $newState
            ? ' (تم حجب جميع التصنيفات الفرعية التابعة له من استقبال بلاغات جديدة)'
            : '';

        return back()->with('success', "تم {$statusText} التصنيف بنجاح{$notice}.");
    }

    /**
     * Delete category with strict safety constraints.
     */
    public function destroy(Category $category): RedirectResponse
    {
        if ($category->children()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف التصنيف الرئيسي لوجود تصنيفات فرعية تابعة له. يرجى حذف التصنيفات الفرعية أو استخدام خيار التعطيل.');
        }

        if ($category->complaints()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف التصنيف لارتباطه ببلاغات تاريخية مسجلة في النظام. يمكنك استخدام خيار التعطيل لحفظ السجلات.');
        }

        $category->delete();

        return back()->with('success', 'تم حذف التصنيف بنجاح.');
    }
}
