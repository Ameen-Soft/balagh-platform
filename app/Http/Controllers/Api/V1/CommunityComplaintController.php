<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\PublicComplaintMapResource;
use App\Http\Resources\PublicComplaintResource;
use App\Services\CommunityInteractionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommunityComplaintController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected CommunityInteractionService $communityService
    ) {}

    /**
     * Display a paginated listing of approved public complaints.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user() ?? auth('sanctum')->user();

        $paginator = $this->communityService->getPublicComplaints(
            $request->query(),
            $user
        );

        return $this->paginatedResponse(
            $paginator,
            PublicComplaintResource::class,
            'تم استرجاع البلاغات العامة بنجاح.'
        );
    }

    /**
     * Display the specified public complaint details without citizen PII.
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $user = $request->user() ?? auth('sanctum')->user();

        $complaint = $this->communityService->getPublicComplaintDetails(
            $id,
            $user
        );

        return $this->successResponse(
            new PublicComplaintResource($complaint),
            'تفاصيل البلاغ العام.'
        );
    }

    /**
     * Retrieve lightweight public complaints within geographic bounds for map pins.
     */
    public function map(Request $request): JsonResponse
    {
        $pins = $this->communityService->getMapComplaints($request->query());

        return $this->successResponse(
            PublicComplaintMapResource::collection($pins),
            'تم جلب نقاط البلاغات على الخريطة بنجاح.'
        );
    }

    /**
     * Add community like to a public complaint.
     */
    public function like(int $id, Request $request): JsonResponse
    {
        $result = $this->communityService->like($id, $request->user());

        $message = $result['already_liked']
            ? 'تم تسجيل الإعجاب مسبقاً.'
            : 'تم تسجيل إعجابك بالبلاغ بنجاح.';

        return $this->successResponse($result, $message);
    }

    /**
     * Remove community like from a public complaint.
     */
    public function unlike(int $id, Request $request): JsonResponse
    {
        $result = $this->communityService->unlike($id, $request->user());

        return $this->successResponse($result, 'تم إلغاء الإعجاب بنجاح.');
    }
}
