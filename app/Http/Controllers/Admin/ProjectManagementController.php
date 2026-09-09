<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectManagementController extends Controller
{
    public function __construct(
        protected ProjectService $projectService
    ) {}

    /**
     * List development projects.
     */
    public function index(): View
    {
        $projects = Project::with(['creator', 'department.ministry'])->latest()->paginate(15);
        $departments = Department::with('ministry')->get();

        return view('admin.projects.index', compact('projects', 'departments'));
    }

    /**
     * Store a new development project.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'description' => 'required|string',
            'target_amount' => 'required|numeric|min:0',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'status' => 'required|in:active,completed,suspended,planning,in_progress',
        ]);

        $validated['current_amount'] = 0;
        $validated['created_by'] = auth()->id();

        Project::create($validated);

        return back()->with('success', 'تم إنشاء المشروع التنموي بنجاح.');
    }

    /**
     * Update project status.
     */
    public function updateProgress(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'current_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,completed,suspended,planning,in_progress',
        ]);

        $project->update($validated);

        return back()->with('success', 'تم تحديث حالة المشروع بنجاح.');
    }
}
