<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\Complaint\AssignFieldWorkerRequest;
use App\Http\Requests\FieldWork\CompleteFieldAssignmentRequest;
use App\Http\Resources\FieldAssignmentResource;
use App\Models\Complaint;
use App\Models\FieldAssignment;
use App\Services\FieldWorkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class FieldAssignmentController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected FieldWorkService $fieldWorkService
    ) {}

    /**
     * List assignments for the current worker or department.
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', FieldAssignment::class);

        $assignments = $this->fieldWorkService->listAssignments(
            $request->user(),
            $request->query('status'),
            (int) $request->query('per_page', 15)
        );

        return $this->paginatedResponse($assignments, FieldAssignmentResource::class, 'قائمة المهام الميدانية.');
    }

    /**
     * Assign a complaint to a field worker.
     */
    public function assign(AssignFieldWorkerRequest $request, Complaint $complaint): JsonResponse
    {
        Gate::authorize('assignFieldWorker', $complaint);

        $assignment = $this->fieldWorkService->assignWorker(
            $complaint,
            (int) $request->validated('user_id'),
            $request->validated('notes'),
            $request->user()
        );

        return $this->successResponse(
            new FieldAssignmentResource($assignment),
            'تم إسناد البلاغ للموظف الميداني بنجاح.',
            201
        );
    }

    /**
     * Start the field assignment.
     */
    public function start(Request $request, FieldAssignment $assignment): JsonResponse
    {
        Gate::authorize('start', $assignment);

        $started = $this->fieldWorkService->startAssignment($assignment, $request->user());

        return $this->successResponse(new FieldAssignmentResource($started), 'تم تسجيل بدء المهمة الميدانية بنجاح.');
    }

    /**
     * Complete the field assignment with geolocation check and evidence uploads.
     */
    public function complete(CompleteFieldAssignmentRequest $request, FieldAssignment $assignment): JsonResponse
    {
        Gate::authorize('complete', $assignment);

        $completed = $this->fieldWorkService->completeAssignment(
            $assignment,
            $request->validated('report'),
            (float) $request->validated('latitude'),
            (float) $request->validated('longitude'),
            $request->file('attachments'),
            $request->user()
        );

        return $this->successResponse(
            new FieldAssignmentResource($completed),
            'تم إتمام المهمة الميدانية وحل البلاغ بنجاح.'
        );
    }
}
