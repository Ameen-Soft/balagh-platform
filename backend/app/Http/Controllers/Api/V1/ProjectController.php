<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\Project\FilterProjectRequest;
use App\Http\Requests\Project\StoreProjectContributionRequest;
use App\Http\Resources\ProjectContributionResource;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected ProjectService $projectService
    ) {}

    /**
     * Display a listing of developmental projects.
     */
    public function index(FilterProjectRequest $request): JsonResponse
    {
        $projects = $this->projectService->listProjects(
            $request->validated(),
            (int) ($request->validated('per_page') ?? 15)
        );

        return $this->paginatedResponse($projects, ProjectResource::class, 'تم استرجاع قائمة المشاريع بنجاح.');
    }

    /**
     * Display the specified project details.
     */
    public function show(Project $project): JsonResponse
    {
        $details = $this->projectService->getProjectDetails($project);

        return $this->successResponse(new ProjectResource($details), 'تفاصيل المشروع.');
    }

    /**
     * Record a financial contribution towards the project.
     */
    public function contribute(StoreProjectContributionRequest $request, Project $project): JsonResponse
    {
        Gate::authorize('contribute', $project);

        $contribution = $this->projectService->recordContribution(
            $project,
            (float) $request->validated('amount'),
            $request->user()
        );

        return $this->successResponse(
            new ProjectContributionResource($contribution),
            'تم تسجيل المساهمة التنموية بنجاح.',
            201
        );
    }
}
