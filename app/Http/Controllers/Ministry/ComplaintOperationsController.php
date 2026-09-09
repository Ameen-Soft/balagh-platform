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
     * List complaints strictly scoped to the Ministry Admin's department.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $deptId = $user->department_id;

        $filters = $request->only(['search', 'status', 'priority', 'category_id', 'date_from', 'date_to', 'sort_by', 'sort_dir']);
        $filters['department_id'] = $deptId; // Strictly scoped

        $complaints = $this->complaintQuery->paginateComplaints($filters, 15);
        $categories = Category::where('department_id', $deptId)->orWhereNull('department_id')->get();

        return view('ministry.complaints.index', compact('complaints', 'categories', 'filters'));
    }

    /**
     * Show complaint dossier for operational processing.
     */
    public function show(int|string $id): View
    {
        $user = auth()->user();
        $complaint = $this->complaintQuery->getComplaintDossier($id);

        // Security check: must belong to user's department/ministry
        if ($complaint->current_department_id !== $user->department_id &&
            $complaint->currentDepartment?->ministry_id !== $user->department?->ministry_id) {
            abort(403, 'لا تملك صلاحية معالجة هذا البلاغ التابع لجهة حكومية أخرى.');
        }

        // Available field workers in this department
        $fieldWorkers = User::where('department_id', $user->department_id)
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
            'status' => 'required|in:under_review,in_progress,resolved,rejected,closed',
            'notes' => 'nullable|string|max:1000',
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
        if ($complaint->current_department_id !== $user->department_id &&
            $complaint->currentDepartment?->ministry_id !== $user->department?->ministry_id) {
            abort(403, 'غير مصرح لك بإجراء عمليات على هذا البلاغ.');
        }
    }
}
