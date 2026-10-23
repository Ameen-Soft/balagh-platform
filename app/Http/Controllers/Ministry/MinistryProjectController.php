<?php

namespace App\Http\Controllers\Ministry;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MinistryProjectController extends Controller
{
    public function __construct(
        protected ProjectService $projectService
    ) {}

    /**
     * Display development projects scoped to the Ministry.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $ministryId = $user->department?->ministry_id;

        $projects = Project::whereHas('department', function ($query) use ($ministryId) {
            if ($ministryId) {
                $query->where('ministry_id', $ministryId);
            }
        })
        ->with(['creator', 'department.ministry'])
        ->latest()
        ->paginate(15);

        $departments = Department::where('ministry_id', $ministryId)->get();
        if ($departments->isEmpty() && $user->department) {
            $departments = collect([$user->department]);
        }

        return view('ministry.projects.index', compact('projects', 'departments'));
    }

    /**
     * Store a new development project created by ministry admin.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Project::class);

        $user = auth()->user();
        $ministryId = $user->department?->ministry_id;

        if (! $request->has('title') && $request->has('name')) {
            $request->merge(['title' => $request->input('name')]);
        }
        if (! $request->has('target_amount') && $request->has('budget')) {
            $request->merge(['target_amount' => $request->input('budget')]);
        }
        if (! $request->has('department_id')) {
            $request->merge(['department_id' => $user->department_id]);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'description' => 'required|string',
            'target_amount' => 'required|numeric|min:0',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'status' => 'required|in:active,completed,suspended,planning,in_progress,draft,published,halted',
            'image' => 'nullable|file|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        // Authorization check: department must belong to user's ministry
        if ($ministryId) {
            $dept = Department::find($validated['department_id']);
            if (! $dept || $dept->ministry_id !== $ministryId) {
                abort(403, 'لا تملك صلاحية إنشاء مشاريع لوزارات أو جهات أخرى.');
            }
        }

        $this->projectService->createProject(
            $validated,
            $request->file('image'),
            $user
        );

        return back()->with('success', 'تم إنشاء المشروع التنموي بنجاح.');
    }

    /**
     * Update project progress/status.
     */
    public function updateProgress(Request $request, Project $project): RedirectResponse
    {
        $user = auth()->user();
        $ministryId = $user->department?->ministry_id;

        if ($ministryId && $project->department?->ministry_id !== $ministryId) {
            abort(403, 'لا تملك صلاحية تعديل هذا المشروع.');
        }

        $validated = $request->validate([
            'current_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,completed,suspended,planning,in_progress,draft,published,halted',
        ]);

        $project->update($validated);

        return back()->with('success', 'تم تحديث حالة المشروع بنجاح.');
    }
}
