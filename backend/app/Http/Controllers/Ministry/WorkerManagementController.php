<?php

namespace App\Http\Controllers\Ministry;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\FieldAssignment;
use App\Models\Role;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WorkerManagementController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Display a listing of field workers belonging to ALL departments of this ministry,
     * with optional department filtering and citizen promotion search.
     */
    public function index(Request $request): View
    {
        $admin = auth()->user();
        $ministryId = $admin->department?->ministry_id;

        if (! $ministryId) {
            abort(403, 'حساب المشرف غير مرتبط بوزارة معتمدة في النظام.');
        }

        // All departments of this ministry for filtering and assignment
        $departments = Department::where('ministry_id', $ministryId)
            ->orderBy('name')
            ->get();
        $departmentIds = $departments->pluck('id')->toArray();

        // 1. Filter workers across ministry
        $selectedDepartmentId = $request->get('department_id');
        if ($selectedDepartmentId && ! in_array((int) $selectedDepartmentId, $departmentIds)) {
            $selectedDepartmentId = null;
        }

        $workersQuery = User::whereIn('department_id', $departmentIds)
            ->whereHas('roles', fn ($q) => $q->where('name', 'Field Worker'))
            ->with('department')
            ->withCount([
                'assignedFieldWork as active_tasks_count' => fn ($q) => $q->whereIn('status', ['pending', 'accepted', 'in_progress']),
                'assignedFieldWork as completed_tasks_count' => fn ($q) => $q->where('status', 'completed'),
                'assignedFieldWork as failed_tasks_count' => fn ($q) => $q->where('status', 'failed'),
            ]);

        if ($selectedDepartmentId) {
            $workersQuery->where('department_id', (int) $selectedDepartmentId);
        }

        $workers = $workersQuery->latest()->get();

        // 2. Search citizens available for promotion
        $search = $request->get('search');
        $citizensQuery = User::whereHas('roles', fn ($q) => $q->where('name', 'Citizen'))
            ->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', ['Super Admin', 'Ministry Admin', 'Field Worker']))
            ->where('is_active', true);

        if ($search) {
            $trimmed = trim($search);
            if (preg_match('/^[0-9+]+$/', $trimmed)) {
                $citizensQuery->where(function ($q) use ($trimmed) {
                    $q->where('phone', 'like', "{$trimmed}%")
                        ->orWhere('national_id', 'like', "{$trimmed}%");
                });
            } elseif (str_contains($trimmed, '@')) {
                $citizensQuery->where('email', 'like', "{$trimmed}%");
            } else {
                $citizensQuery->where(function ($q) use ($trimmed) {
                    $q->where('name', 'like', "{$trimmed}%")
                        ->orWhere('email', 'like', "{$trimmed}%")
                        ->orWhere('phone', 'like', "{$trimmed}%")
                        ->orWhere('national_id', 'like', "{$trimmed}%");
                });
            }
        }

        $citizens = $citizensQuery->latest()->paginate(10)->withQueryString();

        return view('ministry.workers.index', compact('workers', 'departments', 'selectedDepartmentId', 'citizens', 'search', 'admin'));
    }

    /**
     * Store a newly created field worker directly under an active department of the ministry.
     */
    public function store(Request $request): RedirectResponse
    {
        $admin = auth()->user();
        $ministryId = $admin->department?->ministry_id;

        if (! $ministryId) {
            abort(403, 'حساب المشرف غير مرتبط بوزارة معتمدة.');
        }

        $validDepartmentIds = Department::where('ministry_id', $ministryId)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone',
            'national_id' => 'nullable|string|max:20|unique:users,national_id',
            'department_id' => ['required', 'integer', Rule::in($validDepartmentIds)],
            'password' => 'required|string|min:8',
        ], [
            'name.required' => 'اسم الموظف الميداني مطلوب.',
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email' => 'صيغة البريد الإلكتروني غير صالحة.',
            'email.unique' => 'البريد الإلكتروني مسجل مسبقاً في النظام.',
            'phone.required' => 'رقم الهاتف مطلوب.',
            'phone.unique' => 'رقم الهاتف مسجل مسبقاً في النظام.',
            'national_id.unique' => 'الرقم الوطني مسجل مسبقاً في النظام.',
            'department_id.required' => 'يرجى اختيار الإدارة الخدمية التابع لها الموظف.',
            'department_id.in' => 'الإدارة الخدمية المحددة غير صالحة أو غير نشطة أو خارج نطاق وزارتك.',
            'password.required' => 'كلمة المرور مطلوبة.',
            'password.min' => 'يجب ألا تقل كلمة المرور عن 8 أحرف.',
        ]);

        $fieldWorkerRole = Role::where('name', 'Field Worker')->firstOrFail();

        DB::transaction(function () use ($validated, $fieldWorkerRole) {
            $worker = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'national_id' => $validated['national_id'] ?? null,
                'department_id' => (int) $validated['department_id'],
                'password' => Hash::make($validated['password']),
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            $worker->roles()->sync([$fieldWorkerRole->id]);
        });

        return redirect()->route('ministry.workers.index')
            ->with('success', "تم إنشاء حساب الموظف الميداني [{$validated['name']}] وتسكينه بالإدارة المختصة بنجاح.");
    }

    /**
     * Display detailed profile and field assignment history for a worker.
     */
    public function show(User $user): View
    {
        $admin = auth()->user();
        $ministryId = $admin->department?->ministry_id;
        $workerMinistryId = $user->department?->ministry_id;

        if (! $workerMinistryId || $workerMinistryId !== $ministryId || ! $user->hasRole('Field Worker')) {
            abort(403, 'غير مصرح لك باستعراض بيانات موظف يتبع جهة أخرى.');
        }

        $user->load(['department.ministry']);

        $tasks = FieldAssignment::where('worker_id', $user->id)
            ->with([
                'complaint:id,title,status,priority,latitude,longitude',
                'complaint.category:id,name',
                'complaint.currentDepartment:id,name',
                'assignedBy:id,name',
                'evidence',
            ])
            ->latest()
            ->paginate(15);

        $tasksStats = [
            'total' => FieldAssignment::where('worker_id', $user->id)->count(),
            'active' => FieldAssignment::where('worker_id', $user->id)->whereIn('status', ['pending', 'accepted', 'in_progress'])->count(),
            'completed' => FieldAssignment::where('worker_id', $user->id)->where('status', 'completed')->count(),
            'failed' => FieldAssignment::where('worker_id', $user->id)->where('status', 'failed')->count(),
        ];

        // All active departments for editing from detail page if needed
        $departments = Department::where('ministry_id', $ministryId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('ministry.workers.show', compact('user', 'tasks', 'tasksStats', 'departments', 'admin'));
    }

    /**
     * Update worker details with active task transfer restriction.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $admin = auth()->user();
        $ministryId = $admin->department?->ministry_id;
        $workerMinistryId = $user->department?->ministry_id;

        if (! $workerMinistryId || $workerMinistryId !== $ministryId || ! $user->hasRole('Field Worker')) {
            abort(403, 'غير مصرح لك بتعديل بيانات موظف يتبع جهة أخرى.');
        }

        if ($user->id === $admin->id) {
            return back()->with('error', 'لا يمكنك تعديل حسابك الشخصي من شاشة إدارة الموظفين.');
        }

        if ($user->hasRole(['Super Admin', 'Ministry Admin'])) {
            return back()->with('error', 'لا يمكن تعديل الحسابات الإدارية من شاشة الموظفين الميدانيين.');
        }

        $validDepartmentIds = Department::where('ministry_id', $ministryId)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('users', 'phone')->ignore($user->id),
            ],
            'national_id' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('users', 'national_id')->ignore($user->id),
            ],
            'department_id' => ['required', 'integer', Rule::in($validDepartmentIds)],
            'password' => 'nullable|string|min:8',
        ], [
            'name.required' => 'اسم الموظف مطلوب.',
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.unique' => 'البريد الإلكتروني مسجل لمستخدم آخر.',
            'phone.required' => 'رقم الهاتف مطلوب.',
            'phone.unique' => 'رقم الهاتف مسجل لمستخدم آخر.',
            'national_id.unique' => 'الرقم الوطني مسجل لمستخدم آخر.',
            'department_id.required' => 'يرجى اختيار الإدارة الخدمية.',
            'department_id.in' => 'الإدارة الخدمية المحددة غير صالحة أو غير نشطة أو خارج نطاق وزارتك.',
            'password.min' => 'يجب ألا تقل كلمة المرور عن 8 أحرف.',
        ]);

        // Restrict changing department if active tasks exist
        $newDeptId = (int) $validated['department_id'];
        if ($newDeptId !== (int) $user->department_id) {
            $hasActiveTasks = FieldAssignment::where('worker_id', $user->id)
                ->whereIn('status', ['pending', 'accepted', 'in_progress'])
                ->exists();

            if ($hasActiveTasks) {
                return back()->with('error', 'لا يمكن نقل الموظف إلى إدارة خدمية أخرى لوجود مهام ميدانية نشطة مسندة إليه حالياً. يرجى إتمام المهام أو نقلها أولاً.');
            }
        }

        DB::transaction(function () use ($user, $validated) {
            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'national_id' => $validated['national_id'] ?? null,
                'department_id' => (int) $validated['department_id'],
            ];

            if (! empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $user->update($updateData);
        });

        return back()->with('success', "تم تحديث بيانات الموظف [{$user->name}] بنجاح.");
    }

    /**
     * Toggle active status of a field worker and invalidate tokens immediately on deactivation.
     */
    public function toggleActive(User $user): RedirectResponse
    {
        $admin = auth()->user();
        $ministryId = $admin->department?->ministry_id;
        $workerMinistryId = $user->department?->ministry_id;

        if (! $workerMinistryId || $workerMinistryId !== $ministryId || ! $user->hasRole('Field Worker')) {
            abort(403, 'غير مصرح لك بتعديل حالة موظف يتبع جهة أخرى.');
        }

        if ($user->id === $admin->id) {
            return back()->with('error', 'لا يمكنك تعديل حالة حسابك الشخصي.');
        }

        $newState = ! $user->is_active;

        DB::transaction(function () use ($user, $newState) {
            $user->update(['is_active' => $newState]);

            if (! $newState) {
                // Immediately revoke all existing Sanctum API tokens
                $user->tokens()->delete();
            }
        });

        $statusText = $newState
            ? 'تم تفعيل حساب الموظف الميداني بنجاح واستعاد صلاحية استلام المهام وتسجيل الدخول.'
            : 'تم تعطيل حساب الموظف الميداني بنجاح وإبطال جميع رموز دخوله وجلساته النشطة فوراً.';

        return back()->with('success', $statusText);
    }

    /**
     * Delete worker only if ZERO historical relations exist.
     */
    public function destroy(User $user): RedirectResponse
    {
        $admin = auth()->user();
        $ministryId = $admin->department?->ministry_id;
        $workerMinistryId = $user->department?->ministry_id;

        if (! $workerMinistryId || $workerMinistryId !== $ministryId || ! $user->hasRole('Field Worker')) {
            abort(403, 'غير مصرح لك بحذف موظف يتبع جهة أخرى.');
        }

        if ($user->id === $admin->id) {
            return back()->with('error', 'لا يمكنك حذف حسابك الشخصي.');
        }

        if ($user->assignedFieldWork()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف الموظف لوجود سجل مهام ميدانية مسندة إليه في النظام. يمكنك استخدام خيار التعطيل لحفظ السجلات.');
        }

        if ($user->dispatchedFieldWork()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف الحساب لمشاركته في إسناد مهام ميدانية.');
        }

        if ($user->complaints()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف الحساب لوجود بلاغات مسجلة باسمه في المنصة.');
        }

        if ($user->transferredComplaints()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف الحساب لمشاركته في عمليات تحويل البلاغات.');
        }

        if ($user->createdProjects()->count() > 0 || $user->projectContributions()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف الحساب لارتباطه بمشاريع ومساهمات تنموية.');
        }

        DB::transaction(function () use ($user) {
            $user->roles()->detach();
            $user->tokens()->delete();
            $user->delete();
        });

        return redirect()->route('ministry.workers.index')->with('success', 'تم حذف حساب الموظف الميداني بنجاح لخلوه من أي سجلات تاريخية.');
    }

    /**
     * Promote an eligible citizen to a Field Worker under the ministry department.
     */
    public function promote(Request $request, User $user): RedirectResponse
    {
        $admin = auth()->user();
        $ministryId = $admin->department?->ministry_id;

        if (! $ministryId) {
            abort(403, 'حساب المشرف غير مرتبط بجهة حكومية.');
        }

        if ($user->id === $admin->id) {
            return back()->with('error', 'لا يمكنك تعديل صلاحيات حسابك الحالي من هذه الشاشة.');
        }

        if ($user->hasRole(['Super Admin', 'Ministry Admin'])) {
            return back()->with('error', 'لا يمكن تحويل حساب إداري إلى موظف ميداني.');
        }

        if (! $user->is_active) {
            return back()->with('error', 'لا يمكن ترقية حساب غير مفعل.');
        }

        $validDepartmentIds = Department::where('ministry_id', $ministryId)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();

        // Optional department selection, defaults to admin's department
        $targetDepartmentId = $request->filled('department_id')
            ? (int) $request->input('department_id')
            : (int) $admin->department_id;

        if (! in_array($targetDepartmentId, $validDepartmentIds)) {
            return back()->with('error', 'الإدارة الخدمية المختارة غير صالحة أو غير نشطة.');
        }

        $fieldWorkerRole = Role::where('name', 'Field Worker')->firstOrFail();

        DB::transaction(function () use ($user, $targetDepartmentId, $fieldWorkerRole, $admin) {
            $user->update(['department_id' => $targetDepartmentId]);
            $user->roles()->sync([$fieldWorkerRole->id]);

            $deptName = Department::find($targetDepartmentId)?->name ?? 'الجهة المختصة';
            $this->notificationService->send(
                userId: $user->id,
                title: 'ترقية الحساب إلى موظف ميداني',
                body: "تم اعتماد حسابك كموظف ميداني رسمي تابع لـ [{$deptName}].",
                type: 'worker_promoted',
                data: ['department_id' => $targetDepartmentId]
            );
        });

        return back()->with('success', "تمت ترقية المستخدم [{$user->name}] إلى موظف ميداني وربطه بالإدارة بنجاح.");
    }

    /**
     * Demote a Field Worker back to a Citizen and detach from department.
     */
    public function demote(Request $request, User $user): RedirectResponse
    {
        $admin = auth()->user();
        $ministryId = $admin->department?->ministry_id;
        $workerMinistryId = $user->department?->ministry_id;

        if (! $workerMinistryId || $workerMinistryId !== $ministryId) {
            abort(403, 'لا تملك صلاحية إدارة موظف تابع لجهة حكومية أخرى.');
        }

        if ($user->id === $admin->id) {
            return back()->with('error', 'لا يمكنك تعديل حسابك الشخصي.');
        }

        if (! $user->hasRole('Field Worker')) {
            return back()->with('error', 'المستخدم المحدد ليس موظفاً ميدانياً.');
        }

        $hasActiveTasks = FieldAssignment::where('worker_id', $user->id)
            ->whereIn('status', ['pending', 'accepted', 'in_progress'])
            ->exists();

        if ($hasActiveTasks) {
            return back()->with('error', 'لا يمكن إلغاء صفة الموظف الميداني لوجود مهام نشطة قيد التنفيذ مسندة إليه حالياً. يرجى إتمام المهام أو نقلها أولاً.');
        }

        $citizenRole = Role::where('name', 'Citizen')->firstOrFail();

        DB::transaction(function () use ($user, $citizenRole) {
            $user->update(['department_id' => null]);
            $user->roles()->sync([$citizenRole->id]);

            $this->notificationService->send(
                userId: $user->id,
                title: 'تحديث الحساب',
                body: 'تمت إعادتك إلى حساب مواطن وإلغاء صفة الموظف الميداني.',
                type: 'worker_demoted',
                data: []
            );
        });

        return back()->with('success', "تم إلغاء صفة الموظف الميداني عن [{$user->name}] وإعادته إلى حساب مواطن بنجاح.");
    }
}
