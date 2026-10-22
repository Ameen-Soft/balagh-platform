<?php

namespace App\Http\Controllers;

use App\Services\UserSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UserSearchController extends Controller
{
    public function __construct(
        protected UserSearchService $userSearchService
    ) {}

    /**
     * Search users with debounced queries, indexing optimizations, and strict server-side scoping.
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => 'required|string|min:1|max:100',
            'scope' => 'required|string|in:ministry_candidates,citizens_for_promotion,department_workers,admin_users',
            'role' => 'nullable|string|max:50',
            'department_id' => 'nullable|integer',
            'limit' => 'nullable|integer|min:1|max:100',
        ], [
            'query.required' => 'يرجى إدخال نص البحث.',
            'query.min' => 'يجب إدخال حرف واحد على الأقل للبحث.',
            'scope.required' => 'نطاق البحث مطلوب.',
            'scope.in' => 'نطاق البحث المحدد غير مدعوم.',
        ]);

        try {
            $results = $this->userSearchService->search(
                query: $validated['query'],
                scope: $validated['scope'],
                authUser: $request->user(),
                filters: [
                    'role' => $validated['role'] ?? null,
                    'department_id' => $validated['department_id'] ?? null,
                    'limit' => $validated['limit'] ?? 15,
                ]
            );

            return response()->json([
                'success' => true,
                'data' => $results,
            ]);
        } catch (AccessDeniedHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إجراء البحث. يرجى المحاولة لاحقاً.',
            ], 500);
        }
    }
}
