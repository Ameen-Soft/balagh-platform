<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of hierarchical categories.
     * Supports filtering by ministry_id, department_id, parent_id, and level.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::query();

        // Enforce active categories only (with active parents) for API consumption
        $query->active();

        // Filter by Ministry ID directly or via department
        if ($request->filled('ministry_id')) {
            $ministryId = (int) $request->query('ministry_id');
            $query->where(function ($q) use ($ministryId) {
                $q->where('ministry_id', $ministryId)
                    ->orWhereHas('department', function ($dq) use ($ministryId) {
                        $dq->where('ministry_id', $ministryId);
                    });
            });
        }

        // Filter by Department ID
        if ($request->filled('department_id')) {
            $query->where('department_id', (int) $request->query('department_id'));
        }

        // Filter by Parent ID
        if ($request->filled('parent_id')) {
            $query->where('parent_id', (int) $request->query('parent_id'));
        } elseif ($request->boolean('root_only')) {
            $query->whereNull('parent_id');
        }

        // Filter by Level
        if ($request->filled('level')) {
            $query->where('level', (int) $request->query('level'));
        }

        // Search by keyword
        if ($request->filled('search')) {
            $search = '%'.$request->query('search').'%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                    ->orWhere('description', 'like', $search);
            });
        }

        $categories = $query->with([
            'children' => fn ($q) => $q->where('is_active', true),
            'department.ministry',
            'parent',
        ])
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        return $this->successResponse(
            CategoryResource::collection($categories),
            'تم جلب قائمة التصنيفات بنجاح.'
        );
    }

    /**
     * Display the specified category.
     */
    public function show(Category $category): JsonResponse
    {
        $category->load(['children', 'parent', 'department.ministry']);

        return $this->successResponse(
            new CategoryResource($category),
            'تفاصيل التصنيف.'
        );
    }
}
