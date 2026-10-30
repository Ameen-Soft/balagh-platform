<?php

namespace App\Http\Controllers\Ministry;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Complaint;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MinistryCategoryController extends Controller
{
    /**
     * Display categories strictly belonging to the logged-in user's ministry.
     */
    public function index(): View
    {
        $user = auth()->user();
        $ministryId = $user->department?->ministry_id;

        if (! $ministryId) {
            abort(403, 'حساب المشرف غير مرتبط بوزارة معتمدة.');
        }

        $ministry = $user->department->ministry;

        $departments = Department::where('ministry_id', $ministryId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $departmentIds = $departments->pluck('id')->toArray();

        // Hierarchical Main Categories of this ministry
        $mainCategories = Category::where(function ($q) use ($ministryId, $departmentIds) {
            $q->where('ministry_id', $ministryId)
                ->orWhereIn('department_id', $departmentIds);
        })
            ->whereNull('parent_id')
            ->with(['children.department', 'children' => fn ($q) => $q->withCount('complaints')])
            ->withCount('complaints')
            ->orderBy('name')
            ->get();

        // Paginated list of categories
        $categories = Category::where(function ($q) use ($ministryId, $departmentIds) {
            $q->where('ministry_id', $ministryId)
                ->orWhereIn('department_id', $departmentIds);
        })
            ->with(['department', 'parent', 'complaints'])
            ->withCount('complaints')
            ->latest()
            ->paginate(15);

        // Root categories belonging to this ministry available for parenting
        $parentCategories = Category::whereNull('parent_id')
            ->where(function ($q) use ($ministryId, $departmentIds) {
                $q->where('ministry_id', $ministryId)
                    ->orWhereIn('department_id', $departmentIds);
            })
            ->orderBy('name')
            ->get();

        // Accurate total complaints for the entire ministry (independent of pagination)
        $totalMinistryComplaints = Complaint::whereHas('currentDepartment', function ($q) use ($ministryId) {
            $q->where('ministry_id', $ministryId);
        })->count();

        return view('ministry.categories.index', compact(
            'categories',
            'mainCategories',
            'departments',
            'parentCategories',
            'ministry',
            'totalMinistryComplaints'
        ));
    }

    /**
     * Store a new category belonging strictly to the ministry.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $ministryId = $user->department?->ministry_id;

        if (! $ministryId) {
            abort(403, 'غير مصرح: الحساب غير مرتبط بوزارة معتمدة.');
        }

        $validDepartmentIds = Department::where('ministry_id', $ministryId)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();

        $isSubcategory = $request->filled('parent_id');

        if (! $isSubcategory) {
            // Main Category (Level 1)
            $deptRules = ['nullable'];
            if ($request->filled('department_id')) {
                $deptRules[] = Rule::in($validDepartmentIds);
            }

            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('categories', 'name')
                        ->where('ministry_id', $ministryId)
                        ->whereNull('parent_id'),
                ],
                'department_id' => $deptRules,
                'description' => 'nullable|string|max:500',
            ], [
                'name.required' => 'اسم التصنيف الرئيسي مطلوب.',
                'name.unique' => 'يوجد تصنيف رئيسي مسجل بهذا الاسم بالفعل في وزارتك.',
                'department_id.in' => 'لا يمكنك اختيار إدارة خدمية تابعة لوزارة أخرى.',
            ]);

            Category::create([
                'name' => $validated['name'],
                'ministry_id' => $ministryId,
                'department_id' => ! empty($validated['department_id']) ? (int) $validated['department_id'] : null,
                'parent_id' => null,
                'description' => $validated['description'] ?? '',
                'level' => 1,
                'is_active' => true,
            ]);

            return redirect()->route('ministry.categories.index')
                ->with('success', 'تم إنشاء التصنيف الرئيسي للوزارة بنجاح.');
        }

        // Subcategory (Level 2)
        if (empty($validDepartmentIds)) {
            throw ValidationException::withMessages([
                'department_id' => 'لا توجد إدارات خدمية مسجلة تحت وزارتك حتى الآن. يرجى تسجيل إدارة خدمية أولاً.',
            ]);
        }

        $parent = Category::with('department')->findOrFail($request->input('parent_id'));

        // Validate parent is a root category (level 1)
        if ($parent->parent_id !== null || $parent->level !== 1) {
            throw ValidationException::withMessages([
                'parent_id' => 'لا يمكن ربط التصنيف الفرعي إلا بتصنيف رئيسي (المستوى الأول).',
            ]);
        }

        // Validate parent belongs to the same ministry
        $parentMinId = $parent->ministry_id ?? $parent->department?->ministry_id;
        if ($parentMinId && $parentMinId !== $ministryId) {
            throw ValidationException::withMessages([
                'parent_id' => 'التصنيف الرئيسي المختار يتبع وزارة أخرى ولا يمكن الربط به.',
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
            'department_id' => [
                'required',
                'integer',
                Rule::in($validDepartmentIds),
            ],
            'parent_id' => 'required|integer|exists:categories,id',
            'description' => 'nullable|string|max:500',
        ], [
            'name.required' => 'اسم التصنيف الفرعي مطلوب.',
            'name.unique' => 'يوجد تصنيف فرعي مسجل بهذا الاسم تحت نفس التصنيف الرئيسي.',
            'department_id.required' => 'يرجى اختيار الإدارة الخدمية المسؤولة.',
            'department_id.in' => 'لا يمكنك اختيار إدارة خدمية تابعة لوزارة أخرى أو خارج نطاق وزارتك.',
        ]);

        Category::create([
            'name' => $validated['name'],
            'ministry_id' => $ministryId,
            'department_id' => (int) $validated['department_id'],
            'parent_id' => $parent->id,
            'description' => $validated['description'] ?? '',
            'level' => 2,
            'is_active' => true,
        ]);

        return redirect()->route('ministry.categories.index')
            ->with('success', 'تم إنشاء تصنيف البلاغات الفرعي وربطه بالإدارة المختصة بنجاح.');
    }

    /**
     * Update an existing category belonging to the ministry.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $user = auth()->user();
        $ministryId = $user->department?->ministry_id;

        $catMinistryId = $category->ministry_id ?? $category->department?->ministry_id ?? $category->parent?->ministry_id;
        if ($catMinistryId !== $ministryId) {
            abort(403, 'غير مصرح لك بتعديل تصنيف تابع لوزارة أخرى.');
        }

        if ($category->level === 1 || empty($category->parent_id)) {
            // Main Category
            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('categories', 'name')
                        ->where('ministry_id', $ministryId)
                        ->whereNull('parent_id')
                        ->ignore($category->id),
                ],
                'description' => 'nullable|string|max:500',
            ], [
                'name.required' => 'اسم التصنيف الرئيسي مطلوب.',
                'name.unique' => 'يوجد تصنيف رئيسي مسجل بهذا الاسم بالفعل في وزارتك.',
            ]);

            $category->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? '',
            ]);

            return back()->with('success', 'تم تحديث بيانات التصنيف الرئيسي بنجاح.');
        }

        // Subcategory
        $validDepartmentIds = Department::where('ministry_id', $ministryId)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')
                    ->where('parent_id', $category->parent_id)
                    ->ignore($category->id),
            ],
            'department_id' => [
                'required',
                'integer',
                Rule::in($validDepartmentIds),
            ],
            'description' => 'nullable|string|max:500',
        ], [
            'name.required' => 'اسم التصنيف الفرعي مطلوب.',
            'name.unique' => 'يوجد تصنيف فرعي مسجل بهذا الاسم تحت نفس التصنيف الرئيسي.',
            'department_id.required' => 'يرجى اختيار الإدارة الخدمية المسؤولة.',
            'department_id.in' => 'الإدارة الخدمية المحددة غير تابعة لوزارتك.',
        ]);

        $category->update([
            'name' => $validated['name'],
            'department_id' => (int) $validated['department_id'],
            'description' => $validated['description'] ?? '',
        ]);

        return back()->with('success', 'تم تحديث التصنيف الفرعي بنجاح (يسري تغيير الإدارة على البلاغات الجديدة فقط).');
    }

    /**
     * Toggle active state for a category in user's ministry.
     */
    public function toggleActive(Category $category): RedirectResponse
    {
        $user = auth()->user();
        $ministryId = $user->department?->ministry_id;

        $catMinistryId = $category->ministry_id ?? $category->department?->ministry_id ?? $category->parent?->ministry_id;
        if ($catMinistryId !== $ministryId) {
            abort(403, 'غير مصرح لك بتعديل تصنيف تابع لوزارة أخرى.');
        }

        $newState = ! $category->is_active;
        $category->update(['is_active' => $newState]);

        $statusText = $newState ? 'تفعيل' : 'تعطيل وأرشفة';
        $notice = $category->level === 1 && ! $newState
            ? ' (تم حجب جميع التصنيفات الفرعية التابعة له من استقبال بلاغات جديدة)'
            : '';

        return back()->with('success', "تم {$statusText} التصنيف بنجاح{$notice}.");
    }

    /**
     * Delete a category belonging to user's ministry with strict safety checks.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $user = auth()->user();
        $ministryId = $user->department?->ministry_id;

        $catMinistryId = $category->ministry_id ?? $category->department?->ministry_id ?? $category->parent?->ministry_id;
        if ($catMinistryId !== $ministryId) {
            abort(403, 'غير مصرح لك بحذف تصنيف تابع لوزارة أخرى.');
        }

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
