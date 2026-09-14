<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\MinistryResource;
use App\Models\Ministry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MinistryController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of active ministries.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Ministry::where('is_active', true);

        if ($request->boolean('with_departments', true)) {
            $query->with(['departments' => function ($q) {
                $q->where('is_active', true)->orderBy('name');
            }]);
        }

        if ($request->filled('search')) {
            $search = '%' . $request->query('search') . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                    ->orWhere('code', 'like', $search);
            });
        }

        $ministries = $query->orderBy('name')->get();

        return $this->successResponse(
            MinistryResource::collection($ministries),
            'تم جلب قائمة الوزارات النشطة بنجاح.'
        );
    }

    /**
     * Display the specified ministry.
     */
    public function show(Ministry $ministry): JsonResponse
    {
        $ministry->load(['departments' => function ($q) {
            $q->where('is_active', true)->orderBy('name');
        }]);

        return $this->successResponse(
            new MinistryResource($ministry),
            'تفاصيل الوزارة.'
        );
    }
}
