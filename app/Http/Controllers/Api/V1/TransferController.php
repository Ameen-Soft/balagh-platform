<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\Complaint\TransferComplaintRequest;
use App\Http\Resources\ComplaintTransferResource;
use App\Models\Complaint;
use App\Services\TransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class TransferController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected TransferService $transferService
    ) {}

    /**
     * Transfer a complaint to another department or ministry.
     */
    public function transfer(TransferComplaintRequest $request, Complaint $complaint): JsonResponse
    {
        Gate::authorize('transfer', $complaint);

        $transfer = $this->transferService->transferComplaint(
            $complaint,
            (int) $request->validated('to_department_id'),
            $request->validated('reason'),
            $request->user()
        );

        return $this->successResponse(
            new ComplaintTransferResource($transfer),
            'تم تحويل البلاغ وتحديث مساره بنجاح.'
        );
    }
}
