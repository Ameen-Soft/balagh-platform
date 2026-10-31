<?php

namespace App\Http\Controllers\Ministry;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\FieldAssignment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MinistryDepartmentController extends Controller
{
    /**
     * Display a listing of departments strictly scoped to the logged-in user's ministry.
     */
    public function index(): View
    {
        $user = auth()->user();
        $ministry = $user->department?->ministry;

        if (! $ministry) {
            abort(403, 'حساب المشرف غير مرتبط بوزارة معتمدة في النظام.');
        }

        $departments = Department::where('ministry_id', $ministry->id)
            ->withCount(['complaints', 'users', 'categories', 'projects'])
            ->latest()
            ->get();

        return view('ministry.departments.index', compact('departments', 'ministry'));
    }

    /**
     * Store a new service department automatically linked to the user's ministry.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $ministry = $user->department?->ministry;

        if (! $ministry) {
            abort(403, 'غير مصرح: الحساب غير مرتبط بوزارة معتمدة.');
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('departments', 'name')->where('ministry_id', $ministry->id),
            ],
            'description' => 'required|string|max:500',
        ], [
            'name.required' => 'اسم الإدارة الخدمية مطلوب.',
            'name.unique' => 'يوجد إدارة مسجلة بهذا الاسم مسبقاً في وزارتك.',
            'description.required' => 'الوصف والاختصاص مطلوب.',
        ]);

        Department::create([
            'ministry_id' => $ministry->id,
            'name' => $validated['name'],
            'description' => $validated['description'],
            'is_active' => true,
        ]);

        return redirect()->route('ministry.departments.index')->with('success', 'تم تسجيل الإدارة الخدمية الفرعية بنجاح.');
    }

    /**
     * Display detailed metrics and related records for a specific department.
     */
    public function show(Department $department): View
    {
        $user = auth()->user();
        $ministry = $user->department?->ministry;

        if (! $ministry || $department->ministry_id !== $ministry->id) {
            abort(403, 'غير مصرح لك باستعراض بيانات إدارة تابعة لوزارة أخرى.');
        }

        // Subcategories linked to this department
        $categories = $department->categories()
            ->with('parent')
            ->withCount('complaints')
            ->latest()
            ->get();

        // Field workers belonging to this department
        $workers = User::where('department_id', $department->id)
            ->whereHas('roles', fn ($q) => $q->where('name', 'Field Worker'))
            ->withCount([
                'assignedFieldWork as active_tasks_count' => fn ($q) => $q->whereIn('status', ['pending', 'accepted', 'in_progress']),
                'assignedFieldWork as completed_tasks_count' => fn ($q) => $q->where('status', 'completed'),
                'assignedFieldWork as failed_tasks_count' => fn ($q) => $q->where('status', 'failed'),
            ])
            ->latest()
            ->get();

        // Complaints metrics
        $totalComplaints = $department->complaints()->count();
        $openComplaints = $department->complaints()
            ->whereIn('status', ['new', 'under_review', 'assigned', 'in_progress', 'reopened'])
            ->count();
        $resolvedComplaints = $department->complaints()
            ->whereIn('status', ['resolved', 'closed'])
            ->count();

        // Recent complaints
        $recentComplaints = $department->complaints()
            ->with(['citizen:id,name,phone', 'category:id,name'])
            ->latest()
            ->take(10)
            ->get();

        // Field task metrics across all complaints of this department
        $fieldAssignmentsBase = FieldAssignment::whereHas('complaint', fn ($q) => $q->where('current_department_id', $department->id));
        $tasksStats = [
            'total' => (clone $fieldAssignmentsBase)->count(),
            'active' => (clone $fieldAssignmentsBase)->whereIn('status', ['pending', 'accepted', 'in_progress'])->count(),
            'completed' => (clone $fieldAssignmentsBase)->where('status', 'completed')->count(),
            'failed' => (clone $fieldAssignmentsBase)->where('status', 'failed')->count(),
        ];

        // Transfer records
        $incomingTransfers = $department->incomingTransfers()
            ->with(['complaint:id,title', 'fromDepartment:id,name', 'transferredBy:id,name'])
            ->latest()
            ->take(5)
            ->get();

        $outgoingTransfers = $department->outgoingTransfers()
            ->with(['complaint:id,title', 'toDepartment:id,name', 'transferredBy:id,name'])
            ->latest()
            ->take(5)
            ->get();

        // Projects
        $projects = $department->projects()
            ->latest()
            ->take(5)
            ->get();

        return view('ministry.departments.show', compact(
            'department',
            'ministry',
            'categories',
            'workers',
            'totalComplaints',
            'openComplaints',
            'resolvedComplaints',
            'recentComplaints',
            'tasksStats',
            'incomingTransfers',
            'outgoingTransfers',
            'projects'
        ));
    }

    /**
     * Update department details strictly scoped to user's ministry.
     */
    public function update(Request $request, Department $department): RedirectResponse
    {
        $user = auth()->user();
        $ministry = $user->department?->ministry;

        if (! $ministry || $department->ministry_id !== $ministry->id) {
            abort(403, 'غير مصرح لك بتعديل بيانات إدارة تابعة لوزارة أخرى.');
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('departments', 'name')
                    ->where('ministry_id', $ministry->id)
                    ->ignore($department->id),
            ],
            'description' => 'required|string|max:500',
        ], [
            'name.required' => 'اسم الإدارة الخدمية مطلوب.',
            'name.unique' => 'يوجد إدارة مسجلة بهذا الاسم مسبقاً في وزارتك.',
            'description.required' => 'الوصف والاختصاص مطلوب.',
        ]);

        DB::transaction(function () use ($department, $validated) {
            $department->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
            ]);
        });

        return back()->with('success', 'تم تحديث بيانات الإدارة الخدمية بنجاح.');
    }

    /**
     * Toggle active/inactive state (archive/activate) for a department.
     */
    public function toggleActive(Department $department): RedirectResponse
    {
        $user = auth()->user();
        $ministry = $user->department?->ministry;

        if (! $ministry || $department->ministry_id !== $ministry->id) {
            abort(403, 'غير مصرح لك بتعديل حالة إدارة تابعة لوزارة أخرى.');
        }

        $activeTasksCount = FieldAssignment::whereHas('complaint', fn ($q) => $q->where('current_department_id', $department->id))
            ->whereIn('status', ['pending', 'accepted', 'in_progress'])
            ->count();

        $newState = ! $department->is_active;
        $department->update(['is_active' => $newState]);

        $statusText = $newState ? 'تفعيل' : 'تعطيل وأرشفة';
        $notice = '';

        if (! $newState && $activeTasksCount > 0) {
            $notice = " (تنبيه: توجد {$activeTasksCount} مهام ميدانية جارية ستستمر حتى إنجازها ولكن لن يتم استقبال بلاغات جديدة للإدارة).";
        }

        return back()->with('success', "تم {$statusText} الإدارة الخدمية بنجاح{$notice}.");
    }

    /**
     * Delete department only if it has zero associated records.
     */
    public function destroy(Department $department): RedirectResponse
    {
        $user = auth()->user();
        $ministry = $user->department?->ministry;

        if (! $ministry || $department->ministry_id !== $ministry->id) {
            abort(403, 'غير مصرح لك بحذف إدارة تابعة لوزارة أخرى.');
        }

        if ($user->department_id === $department->id) {
            return back()->with('error', 'لا يمكن حذف الإدارة المرتبطة بحسابك الحالي كمشرف للوزارة.');
        }

        if ($department->users()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف الإدارة لوجود موظفين أو مستخدمين مسجلين عليها. يرجى نقل الموظفين أولاً أو استخدام خيار التعطيل.');
        }

        if ($department->categories()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف الإدارة لوجود تصنيفات بلاغات تابعة لها. يرجى نقل أو حذف التصنيفات أولاً، أو استخدام خيار التعطيل.');
        }

        if ($department->complaints()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف الإدارة لوجود بلاغات تاريخية مسجلة عليها في النظام. يمكنك استخدام خيار التعطيل لحفظ السجلات.');
        }

        if ($department->incomingTransfers()->count() > 0 || $department->outgoingTransfers()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف الإدارة لوجود سجلات تحويل بلاغات تاريخية مرتبطة بها. يرجى استخدام خيار التعطيل.');
        }

        if ($department->projects()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف الإدارة لوجود مشاريع تنموية مسجلة عليها. يرجى استخدام خيار التعطيل.');
        }

        DB::transaction(function () use ($department) {
            $department->delete();
        });

        return redirect()->route('ministry.departments.index')->with('success', 'تم حذف الإدارة الخدمية بنجاح.');
    }
}
