<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Throwable;

class NotificationService
{
    /**
     * Get paginated notifications for a specific user.
     */
    public function getUserNotifications(int $userId, ?bool $unreadOnly = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = Notification::where('user_id', $userId);

        if ($unreadOnly) {
            $query->where('is_read', false);
        }

        return $query->latest('created_at')->latest('id')->paginate($perPage);
    }
    /**
     * Send a notification to a specific user.
     * Wrapped in try-catch to ensure notification side-effects never break primary transactions.
     */
    public function send(int $userId, string $title, string $body, string $type = 'general', array $data = []): ?Notification
    {
        try {
            return Notification::create([
                'user_id' => $userId,
                'title' => $title,
                'body' => $body,
                'type' => $type,
                'data' => $data,
                'is_read' => false,
            ]);
        } catch (Throwable $e) {
            Log::warning('Failed to send notification to user ' . $userId . ': ' . $e->getMessage(), [
                'type' => $type,
                'title' => $title,
            ]);

            return null;
        }
    }

    /**
     * Send notifications to multiple users.
     *
     * @param  array<int>  $userIds
     * @return array<Notification>
     */
    public function sendToMany(array $userIds, string $title, string $body, string $type = 'general', array $data = []): array
    {
        $created = [];
        foreach ($userIds as $userId) {
            $notification = $this->send($userId, $title, $body, $type, $data);
            if ($notification) {
                $created[] = $notification;
            }
        }

        return $created;
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        return (bool) Notification::where('id', $notificationId)
            ->where('user_id', $userId)
            ->update(['is_read' => true]);
    }

    /**
     * Mark all notifications for a user as read.
     */
    public function markAllAsRead(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }
}
