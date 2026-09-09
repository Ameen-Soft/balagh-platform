<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Ministry;
use App\Services\Queries\StatisticsQueryService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected StatisticsQueryService $statsQuery
    ) {}

    /**
     * Display Super Admin Global Dashboard.
     */
    public function index(): View
    {
        $overview = $this->statsQuery->getGlobalOverview();
        $statusCounts = $this->statsQuery->getComplaintsByStatus();
        $categoryCounts = $this->statsQuery->getComplaintsByCategory();

        // Top ministries with complaint counts
        $ministries = Ministry::withCount('departments')
            ->with(['departments.complaints' => fn ($q) => $q->latest()->take(5)])
            ->get();

        // Recent nationwide complaints for real-time monitoring
        $recentComplaints = Complaint::with(['citizen:id,name', 'category:id,name', 'currentDepartment.ministry:id,name'])
            ->latest()
            ->take(8)
            ->get();

        return view('admin.dashboard', compact('overview', 'statusCounts', 'categoryCounts', 'ministries', 'recentComplaints'));
    }
}
