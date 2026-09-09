<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Complaint;
use App\Models\Ministry;
use App\Services\Queries\ComplaintQueryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComplaintMonitoringController extends Controller
{
    public function __construct(
        protected ComplaintQueryService $complaintQuery
    ) {}

    /**
     * Display nationwide complaints list with advanced filters for global monitoring.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'ministry_id', 'department_id', 'status', 'priority', 'category_id', 'date_from', 'date_to', 'sort_by', 'sort_dir']);
        $complaints = $this->complaintQuery->paginateComplaints($filters, 15);
        $ministries = Ministry::with('departments')->get();
        $categories = Category::all();

        return view('admin.complaints.index', compact('complaints', 'ministries', 'categories', 'filters'));
    }

    /**
     * Display detailed complaint dossier for Super Admin monitoring (Read-only dossier).
     */
    public function show(int|string $id): View
    {
        $complaint = $this->complaintQuery->getComplaintDossier($id);

        return view('admin.complaints.show', compact('complaint'));
    }
}
