<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class NotificationController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Display a paginated listing of notifications for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $unreadOnly = $request->boolean('unread_only');
        $perPage = (int) $request->query('per_page', 15);

        $notifications = $this->notificationService->getUserNotifications(
            $request->user()->id,
            $unreadOnly,
            $perPage
        );

        return $this->paginatedResponse(
            $notifications,
            NotificationResource::class,
            'قائمة إشعارات المستخدم.'
        );
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        Gate::authorize('update', $notification);

        $this->notificationService->markAsRead($notification->id, $request->user()->id);

        return $this->successResponse(
            new NotificationResource($notification->fresh()),
            'تم تأشير الإشعار كمقروء بنجاح.'
        );
    }

    /**
     * Mark all notifications for the authenticated user as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $count = $this->notificationService->markAllAsRead($request->user()->id);

        return $this->successResponse(
            ['updated_count' => $count],
            'تم تأشير جميع الإشعارات كمقروءة بنجاح.'
        );
    }
}
