<?php

namespace App\Http\Controllers\Ministry;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\FieldAssignment;
use App\Services\Queries\StatisticsQueryService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected StatisticsQueryService $statsQuery
    ) {}

    /**
     * Display the operational dashboard for Ministry Admin.
     */
    public function index(): View
    {
        $user = auth()->user();
        $ministryId = $user->department?->ministry_id;

        if (! $ministryId) {
            abort(403, 'حساب المشرف غير مرتبط بوزارة معتمدة في النظام.');
        }

        $deptOverview = $this->statsQuery->getDepartmentOverview($user->department_id);

        // Urgent complaints across all departments in this ministry
        $urgentComplaints = Complaint::with(['citizen:id,name,phone', 'category:id,name', 'currentDepartment:id,name'])
            ->whereHas('currentDepartment', fn ($q) => $q->where('ministry_id', $ministryId))
            ->where('priority', 'urgent')
            ->whereNotIn('status', ['resolved', 'closed', 'rejected'])
            ->latest()
            ->take(5)
            ->get();

        // Recent incoming complaints across all departments in this ministry
        $recentComplaints = Complaint::with(['citizen:id,name', 'category:id,name', 'currentDepartment:id,name'])
            ->whereHas('currentDepartment', fn ($q) => $q->where('ministry_id', $ministryId))
            ->latest()
            ->take(8)
            ->get();

        // Recent active field assignments in this ministry
        $recentAssignments = FieldAssignment::with(['worker:id,name', 'complaint:id,title'])
            ->whereHas('complaint.currentDepartment', fn ($q) => $q->where('ministry_id', $ministryId))
            ->latest()
            ->take(5)
            ->get();

        return view('ministry.dashboard', compact('deptOverview', 'urgentComplaints', 'recentComplaints', 'recentAssignments'));
    }
}
