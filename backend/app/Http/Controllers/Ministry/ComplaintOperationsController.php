<?php

namespace App\Http\Controllers\Ministry;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Complaint;
use App\Models\Department;
use App\Models\User;
use App\Services\ComplaintService;
use App\Services\FieldWorkService;
use App\Services\Queries\ComplaintQueryService;
use App\Services\TransferService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComplaintOperationsController extends Controller
{
    public function __construct(
        protected ComplaintQueryService $complaintQuery,
        protected ComplaintService $complaintService,
        protected FieldWorkService $fieldWorkService,
        protected TransferService $transferService
    ) {}

    /**
     * List complaints strictly scoped to the Ministry Admin's ministry with optional department filter.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $ministryId = $user->department?->ministry_id;

        if (! $ministryId) {
            abort(403, 'حساب المشرف غير مرتبط بوزارة معتمدة في النظام.');
        }

        $departments = Department::where('ministry_id', $ministryId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $validDeptIds = $departments->pluck('id')->toArray();

        $filters = $request->only(['search', 'status', 'priority', 'category_id', 'department_id', 'date_from', 'date_to', 'sort_by', 'sort_dir']);
        $filters['ministry_id'] = $ministryId; // Always scoped to user's ministry

        if ($request->filled('department_id') && $request->get('department_id') !== 'all') {
            $selectedDeptId = (int) $request->get('department_id');
            if (! in_array($selectedDeptId, $validDeptIds, true)) {
                abort(403, 'الإدارة الخدمية المحددة غير تابعة لوزارتك أو غير صالحة.');
            }
            $filters['department_id'] = $selectedDeptId;
        } else {
            unset($filters['department_id']);
        }

        $complaints = $this->complaintQuery->paginateComplaints($filters, 15);
        $categories = Category::whereIn('department_id', $validDeptIds)->orWhereNull('department_id')->get();

        return view('ministry.complaints.index', compact('complaints', 'categories', 'departments', 'filters'));
    }

    /**
     * Show complaint dossier for operational processing.
     */
    public function show(int|string $id): View
    {
        $user = auth()->user();
        $ministryId = $user->department?->ministry_id;

        if (! $ministryId) {
            abort(403, 'حساب المشرف غير مرتبط بوزارة معتمدة في النظام.');
        }

        $complaint = $this->complaintQuery->getComplaintDossier($id);

        // Security check: must belong to user's ministry
        if ($complaint->currentDepartment?->ministry_id !== $ministryId) {
            abort(403, 'لا تملك صلاحية معالجة هذا البلاغ التابع لجهة حكومية أخرى.');
        }

        // Available field workers across this ministry
        $ministryDeptIds = Department::where('ministry_id', $ministryId)->pluck('id');
        $fieldWorkers = User::whereIn('department_id', $ministryDeptIds)
            ->where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->where('name', 'Field Worker'))
            ->get();

        // Target departments for potential transfer
        $otherDepartments = Department::with('ministry')
            ->where('id', '!=', $complaint->current_department_id)
            ->get();

        return view('ministry.complaints.show', compact('complaint', 'fieldWorkers', 'otherDepartments'));
    }

    /**
     * Operational action: Change complaint status.
     */
    public function updateStatus(Request $request, Complaint $complaint): RedirectResponse
    {
        $user = auth()->user();
        $this->authorizeComplaintAccess($complaint, $user);

        $validated = $request->validate([
            'status' => 'required|in:under_review,in_progress,resolved,rejected,closed,reopened',
            'notes' => 'required_if:status,rejected|nullable|string|max:1000',
        ], [
            'notes.required_if' => 'سبب رفض البلاغ مطلوب وإلزامي لتوضيح القرار للمواطن.',
        ]);

        $this->complaintService->updateStatus($complaint, $validated['status'], $validated['notes'] ?? null, $user);

        return back()->with('success', 'تم تحديث حالة البلاغ وتوثيق العملية في السجل الزمني بنجاح.');
    }

    /**
     * Operational action: Assign complaint to a field worker.
     */
    public function assignWorker(Request $request, Complaint $complaint): RedirectResponse
    {
        $user = auth()->user();
        $this->authorizeComplaintAccess($complaint, $user);

        $validated = $request->validate([
            'worker_id' => 'required|exists:users,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $this->fieldWorkService->assignWorker($complaint, (int) $validated['worker_id'], $validated['notes'] ?? null, $user);

        return back()->with('success', 'تم إسناد المهمة الميدانية للموظف المختص وتحديث حالة البلاغ بنجاح.');
    }

    /**
     * Operational action: Transfer complaint to another department.
     */
    public function transfer(Request $request, Complaint $complaint): RedirectResponse
    {
        $user = auth()->user();
        $this->authorizeComplaintAccess($complaint, $user);

        $validated = $request->validate([
            'to_department_id' => 'required|exists:departments,id',
            'reason' => 'required|string|min:5|max:1000',
        ]);

        $this->transferService->transferComplaint($complaint, (int) $validated['to_department_id'], $validated['reason'], $user);

        return redirect()->route('ministry.complaints.index')->with('success', 'تمت إحالة البلاغ إلى الجهة المختصة بنجاح.');
    }

    /**
     * Helper to verify ministry admin has authority on this complaint.
     */
    protected function authorizeComplaintAccess(Complaint $complaint, User $user): void
    {
        $ministryId = $user->department?->ministry_id;
        if (! $ministryId || $complaint->currentDepartment?->ministry_id !== $ministryId) {
            abort(403, 'غير مصرح لك بإجراء عمليات على هذا البلاغ.');
        }
    }
}
