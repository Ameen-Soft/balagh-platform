<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\ComplaintLike;
use App\Models\ComplaintTimeline;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Throwable;

class CommunityEngagementService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Inspect likes count against trending threshold and dispatch administrative alerts if crossed.
     * Note: In alignment with architectural requirements, this does NOT alter official operational priority.
     */
    public function checkAndTriggerTrendingAlert(Complaint $complaint, ?int $performerId = null): bool
    {
        $threshold = (int) config('balagh.community.trending_threshold', 15);

        if ($complaint->likes_count < $threshold) {
            return false;
        }

        if ($complaint->community_alerted_at !== null) {
            return false;
        }

        try {
            // Mark alerted timestamp to avoid duplicate alerts
            $complaint->update([
                'community_alerted_at' => now(),
            ]);

            // Add timeline event
            ComplaintTimeline::create([
                'complaint_id' => $complaint->id,
                'event_type' => 'community_trending_alert',
                'description' => "وصل البلاغ إلى مستوى تفاعل مجتمعي لافت ({$complaint->likes_count} إعجاباً).",
                'old_value' => null,
                'new_value' => (string) $complaint->likes_count,
                'performed_by' => $performerId ?? $complaint->citizen_id,
            ]);

            // Find relevant admins for notification
            $adminQuery = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['Ministry Admin', 'Super Admin']);
            });

            if ($complaint->current_department_id) {
                $adminQuery->where(function ($q) use ($complaint) {
                    $q->where('department_id', $complaint->current_department_id)
                        ->orWhereNull('department_id');
                });
            }

            $adminIds = $adminQuery->pluck('id')->all();

            if (! empty($adminIds)) {
                $this->notificationService->sendToMany(
                    userIds: $adminIds,
                    title: 'اهتمام مجتمعي لافت',
                    body: "البلاغ '{$complaint->title}' حقق اهتماماً مجتمعياً لافتاً بوصوله إلى {$complaint->likes_count} إعجاباً.",
                    type: 'community_trending_alert',
                    data: [
                        'complaint_id' => $complaint->id,
                        'likes_count' => $complaint->likes_count,
                    ]
                );
            }

            // Also notify the citizen owner
            if ($complaint->citizen_id) {
                $this->notificationService->send(
                    userId: $complaint->citizen_id,
                    title: 'تفاعل مجتمعي واسع',
                    body: "حظي بلاغك '{$complaint->title}' باهتمام مجتمعي واسع وتجاوز {$complaint->likes_count} إعجاباً.",
                    type: 'community_trending_citizen',
                    data: [
                        'complaint_id' => $complaint->id,
                        'likes_count' => $complaint->likes_count,
                    ]
                );
            }

            return true;
        } catch (Throwable $e) {
            Log::warning('Failed to trigger community trending alert: '.$e->getMessage(), [
                'complaint_id' => $complaint->id,
            ]);

            return false;
        }
    }

    /**
     * Reconcile cached likes_count with actual rows in complaint_likes table.
     */
    public function reconcileLikesCount(int $complaintId): int
    {
        $actualCount = ComplaintLike::where('complaint_id', $complaintId)->count();

        Complaint::where('id', $complaintId)->update([
            'likes_count' => $actualCount,
        ]);

        return $actualCount;
    }
}
