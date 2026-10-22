<?php

namespace App\Http\Controllers\Ministry;

use App\Http\Controllers\Controller;
use App\Models\FieldAssignment;
use App\Models\Role;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WorkerManagementController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Display a listing of field workers belonging to this ministry department,
     * and search eligible citizens for promotion.
     */
    public function index(Request $request): View
    {
        $admin = auth()->user();
        $departmentId = $admin->department_id;

        if (! $departmentId) {
            abort(403, 'حساب المشرف غير مرتبط بجهة حكومية أو قسم معتمد.');
        }

        // 1. Current field workers in this department with task metrics
        $workers = User::where('department_id', $departmentId)
            ->whereHas('roles', fn ($q) => $q->where('name', 'Field Worker'))
            ->withCount([
                'assignedFieldWork as active_tasks_count' => fn ($q) => $q->whereIn('status', ['pending', 'accepted', 'in_progress']),
                'assignedFieldWork as completed_tasks_count' => fn ($q) => $q->where('status', 'completed'),
            ])
            ->latest()
            ->get();

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

        return view('ministry.workers.index', compact('workers', 'citizens', 'search', 'admin'));
    }

    /**
     * Promote an eligible citizen to a Field Worker under the ministry department.
     */
    public function promote(Request $request, User $user): RedirectResponse
    {
        $admin = auth()->user();
        $departmentId = $admin->department_id;

        if (! $departmentId) {
            abort(403, 'حساب المشرف غير مرتبط بجهة حكومية.');
        }

        // Prevent self-promotion or promoting administrative roles
        if ($user->id === $admin->id) {
            return back()->with('error', 'لا يمكنك تعديل صلاحيات حسابك الحالي من هذه الشاشة.');
        }

        if ($user->hasRole(['Super Admin', 'Ministry Admin'])) {
            return back()->with('error', 'لا يمكن تحويل حساب إداري إلى موظف ميداني.');
        }

        if (! $user->is_active) {
            return back()->with('error', 'لا يمكن ترقية حساب غير مفعل.');
        }

        $fieldWorkerRole = Role::where('name', 'Field Worker')->firstOrFail();

        DB::transaction(function () use ($user, $departmentId, $fieldWorkerRole, $admin) {
            // Assign department and field worker role
            $user->update(['department_id' => $departmentId]);
            $user->roles()->sync([$fieldWorkerRole->id]);

            // Notify user
            $departmentName = $admin->department?->name ?? 'الجهة المختصة';
            $this->notificationService->send(
                userId: $user->id,
                title: 'ترقية الحساب إلى موظف ميداني',
                body: "تم اعتماد حسابك كموظف ميداني رسمي تابع لـ [{$departmentName}].",
                type: 'worker_promoted',
                data: ['department_id' => $departmentId]
            );
        });

        return back()->with('success', "تمت ترقية المستخدم [{$user->name}] إلى موظف ميداني وربطه بالقسم بنجاح.");
    }

    /**
     * Demote a Field Worker back to a Citizen and detach from department.
     */
    public function demote(Request $request, User $user): RedirectResponse
    {
        $admin = auth()->user();
        $departmentId = $admin->department_id;

        if (! $departmentId) {
            abort(403, 'حساب المشرف غير مرتبط بجهة حكومية.');
        }

        // Check department scope
        if ($user->department_id !== $departmentId) {
            abort(403, 'لا تملك صلاحية إدارة موظف تابع لجهة حكومية أخرى.');
        }

        if ($user->id === $admin->id) {
            return back()->with('error', 'لا يمكنك تعديل حسابك الشخصي.');
        }

        if (! $user->hasRole('Field Worker')) {
            return back()->with('error', 'المستخدم المحدد ليس موظفاً ميدانياً.');
        }

        // Check for active assignments
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
