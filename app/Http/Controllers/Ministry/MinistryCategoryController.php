<?php

namespace App\Http\Controllers\Ministry;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MinistryCategoryController extends Controller
{
    /**
     * Display categories strictly belonging to the logged-in user's ministry departments.
     */
    public function index(): View
    {
        $user = auth()->user();
        $ministry = $user->department?->ministry;

        if (! $ministry) {
            abort(403, 'حساب المشرف غير مرتبط بوزارة معتمدة.');
        }

        $departments = Department::where('ministry_id', $ministry->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $departmentIds = $departments->pluck('id');

        $categories = Category::whereIn('department_id', $departmentIds)
            ->with(['department', 'parent', 'complaints'])
            ->withCount('complaints')
            ->latest()
            ->paginate(15);

        // Root categories belonging to this ministry available for parenting
        $parentCategories = Category::whereNull('parent_id')
            ->whereIn('department_id', $departmentIds)
            ->orderBy('name')
            ->get();

        return view('ministry.categories.index', compact('categories', 'departments', 'parentCategories', 'ministry'));
    }

    /**
     * Store a new category belonging strictly to the ministry and one of its departments.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $ministry = $user->department?->ministry;

        if (! $ministry) {
            abort(403, 'غير مصرح: الحساب غير مرتبط بوزارة معتمدة.');
        }

        $validDepartmentIds = Department::where('ministry_id', $ministry->id)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();

        if (empty($validDepartmentIds)) {
            throw ValidationException::withMessages([
                'department_id' => 'لا توجد إدارات خدمية مسجلة تحت وزارتك حتى الآن. يرجى تسجيل إدارة خدمية أولاً.',
            ]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => [
                'required',
                'integer',
                Rule::in($validDepartmentIds),
            ],
            'parent_id' => 'nullable|integer|exists:categories,id',
            'description' => 'nullable|string|max:500',
        ], [
            'name.required' => 'اسم التصنيف مطلوب.',
            'department_id.required' => 'يرجى اختيار الإدارة الخدمية المسؤولة.',
            'department_id.in' => 'لا يمكنك اختيار إدارة خدمية تابعة لوزارة أخرى أو خارج نطاق وزارتك.',
            'parent_id.exists' => 'التصنيف الرئيسي المحدد غير صالح أو غير موجود.',
        ]);

        $level = 1;
        if (! empty($validated['parent_id'])) {
            $parent = Category::with('department')->findOrFail($validated['parent_id']);

            // Validate parent is a root category (level 1 and parent_id null)
            if ($parent->parent_id !== null || $parent->level !== 1) {
                throw ValidationException::withMessages([
                    'parent_id' => 'لا يمكن ربط التصنيف الفرعي إلا بتصنيف رئيسي (المستوى الأول).',
                ]);
            }

            // Validate parent belongs to the same ministry
            if ($parent->department && $parent->department->ministry_id !== $ministry->id) {
                throw ValidationException::withMessages([
                    'parent_id' => 'التصنيف الرئيسي المختار يتبع وزارة أخرى ولا يمكن الربط به.',
                ]);
            }

            $level = 2;
        }

        Category::create([
            'name' => $validated['name'],
            'department_id' => $validated['department_id'],
            'parent_id' => $validated['parent_id'] ?? null,
            'description' => $validated['description'] ?? '',
            'level' => $level,
        ]);

        return redirect()->route('ministry.categories.index')->with('success', 'تم إنشاء تصنيف البلاغات وربطه بالإدارة المختصة بنجاح.');
    }
}
