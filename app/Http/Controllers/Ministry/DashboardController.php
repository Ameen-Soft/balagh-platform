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
        $deptId = $user->department_id;

        $deptOverview = $this->statsQuery->getDepartmentOverview($deptId);

        // Urgent complaints in this department
        $urgentComplaints = Complaint::with(['citizen:id,name,phone', 'category:id,name'])
            ->where('current_department_id', $deptId)
            ->where('priority', 'urgent')
            ->whereNotIn('status', ['resolved', 'closed', 'rejected'])
            ->latest()
            ->take(5)
            ->get();

        // Recent incoming complaints
        $recentComplaints = Complaint::with(['citizen:id,name', 'category:id,name'])
            ->where('current_department_id', $deptId)
            ->latest()
            ->take(8)
            ->get();

        // Recent active field assignments
        $recentAssignments = FieldAssignment::with(['worker:id,name', 'complaint:id,title'])
            ->whereHas('complaint', fn ($q) => $q->where('current_department_id', $deptId))
            ->latest()
            ->take(5)
            ->get();

        return view('ministry.dashboard', compact('deptOverview', 'urgentComplaints', 'recentComplaints', 'recentAssignments'));
    }
}
