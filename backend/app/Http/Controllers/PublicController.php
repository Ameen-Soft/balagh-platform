<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Project;
use App\Services\Queries\ComplaintQueryService;
use App\Services\Queries\StatisticsQueryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function __construct(
        protected StatisticsQueryService $statsQuery,
        protected ComplaintQueryService $complaintQuery
    ) {}

    /**
     * Public Landing Page.
     */
    public function welcome(): View
    {
        $stats = $this->statsQuery->getPublicTransparencyStats();
        $recentResolved = $this->complaintQuery->paginatePublicComplaints(['status' => 'resolved'], 6);
        $featuredProjects = Project::with('creator')->latest()->take(3)->get();
        $categories = Category::withCount('complaints')->orderByDesc('complaints_count')->take(6)->get();

        return view('welcome', compact('stats', 'recentResolved', 'featuredProjects', 'categories'));
    }

    /**
     * Public Transparency Portal - Complaints Registry.
     */
    public function complaintsIndex(Request $request): View
    {
        $filters = $request->only(['search', 'category_id', 'status']);
        $complaints = $this->complaintQuery->paginatePublicComplaints($filters, 12);
        $categories = Category::all();

        return view('public.complaints.index', compact('complaints', 'categories', 'filters'));
    }

    /**
     * Public Complaint Detail with sensitive data masked.
     */
    public function complaintShow(int|string $id): View
    {
        $complaint = $this->complaintQuery->getPublicComplaintDetail($id);

        return view('public.complaints.show', compact('complaint'));
    }

    /**
     * Public Projects Directory.
     */
    public function projectsIndex(Request $request): View
    {
        $status = $request->get('status');
        $query = Project::with('creator');

        if ($status && in_array($status, ['planning', 'in_progress', 'completed', 'halted'])) {
            $query->where('status', $status);
        }

        $projects = $query->latest()->paginate(9);

        return view('public.projects.index', compact('projects', 'status'));
    }

    /**
     * Public Project Detail.
     */
    public function projectShow(int|string $id): View
    {
        $project = Project::with([
            'creator',
            'contributions' => fn ($q) => $q->latest()->take(10),
            'contributions.contributor',
        ])->findOrFail($id);

        return view('public.projects.show', compact('project'));
    }
}
