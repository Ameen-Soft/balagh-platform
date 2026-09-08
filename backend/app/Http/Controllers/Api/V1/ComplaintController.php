<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\Complaint\FilterComplaintRequest;
use App\Http\Requests\Complaint\StoreComplaintRequest;
use App\Http\Requests\Complaint\UpdateComplaintStatusRequest;
use App\Http\Resources\ComplaintResource;
use App\Models\Complaint;
use App\Services\ComplaintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ComplaintController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected ComplaintService $complaintService
    ) {}

    /**
     * Display a listing of complaints scoped by user permissions and filters.
     */
    public function index(FilterComplaintRequest $request): JsonResponse
    {
        Gate::authorize('viewAny', Complaint::class);

        $complaints = $this->complaintService->listComplaints(
            $request->validated(),
            $request->user()
        );

        return $this->paginatedResponse($complaints, ComplaintResource::class, 'تم استرجاع قائمة البلاغات بنجاح.');
    }

    /**
     * Store a newly created complaint with business rules, routing, and notifications.
     */
    public function store(StoreComplaintRequest $request): JsonResponse
    {
        Gate::authorize('create', Complaint::class);

        $complaint = $this->complaintService->createComplaint(
            $request->validated(),
            $request->user(),
            $request->file('attachments')
        );

        return $this->successResponse(
            new ComplaintResource($complaint),
            'تم تسجيل البلاغ وتوجيهه للإدارة المختصة بنجاح.',
            201
        );
    }

    /**
     * Display the specified complaint details with history, attachments, and timeline.
     */
    public function show(Complaint $complaint): JsonResponse
    {
        Gate::authorize('view', $complaint);

        $details = $this->complaintService->getComplaintDetails($complaint);

        return $this->successResponse(new ComplaintResource($details), 'تفاصيل البلاغ.');
    }

    /**
     * Update the status of the complaint with atomic timeline logging.
     */
    public function updateStatus(UpdateComplaintStatusRequest $request, Complaint $complaint): JsonResponse
    {
        $newStatus = $request->validated('status');

        if ($newStatus === 'closed') {
            Gate::authorize('close', $complaint);
        } elseif ($newStatus === 'rejected') {
            Gate::authorize('reject', $complaint);
        } else {
            Gate::authorize('update', $complaint);
        }

        $updated = $this->complaintService->updateStatus(
            $complaint,
            $newStatus,
            $request->validated('notes'),
            $request->user()
        );

        return $this->successResponse(new ComplaintResource($updated), 'تم تحديث حالة البلاغ بنجاح.');
    }
}
