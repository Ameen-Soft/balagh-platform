<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\ComplaintLike;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CommunityInteractionService
{
    /**
     * Allowed statuses for public community visibility.
     * Note: Excludes 'new' (until reviewed) and 'rejected'.
     */
    protected const ALLOWED_STATUSES = [
        'under_review',
        'assigned',
        'in_progress',
        'resolved',
        'closed',
    ];

    public function __construct(
        protected CommunityEngagementService $engagementService
    ) {}

    /**
     * Get paginated public complaints feed with privacy redaction and likes filtering.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPublicComplaints(array $filters = [], ?User $user = null): LengthAwarePaginator
    {
        $query = Complaint::query()
            ->where('is_public', true)
            ->whereIn('status', self::ALLOWED_STATUSES);

        // Filter by category
        if (! empty($filters['category_id'])) {
            $query->where('category_id', (int) $filters['category_id']);
        }

        // Filter by department
        if (! empty($filters['department_id'])) {
            $query->where('current_department_id', (int) $filters['department_id']);
        }

        // Filter by specific status (strictly within allowed statuses)
        if (! empty($filters['status']) && in_array($filters['status'], self::ALLOWED_STATUSES, true)) {
            $query->where('status', $filters['status']);
        }

        // Keyword search
        if (! empty($filters['search'])) {
            $searchTerm = '%' . trim((string) $filters['search']) . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm);
            });
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'popular';
        if ($sortBy === 'latest') {
            $query->latest('id');
        } elseif ($sortBy === 'oldest') {
            $query->oldest('id');
        } else {
            // Default to popular: most likes first, then latest
            $query->orderByDesc('likes_count')->latest('id');
        }

        // Relationships (excluding citizen sensitive PII)
        $query->with([
            'category:id,name',
            'currentDepartment:id,name,ministry_id',
            'currentDepartment.ministry:id,name',
            'attachments',
        ]);

        if ($user) {
            $query->with(['likes' => function ($q) use ($user) {
                $q->where('user_id', $user->id);
            }]);
        }

        $perPage = max(1, min((int) ($filters['per_page'] ?? 15), 50));

        return $query->paginate($perPage);
    }

    /**
     * Retrieve single public complaint details.
     */
    public function getPublicComplaintDetails(int $complaintId, ?User $user = null): Complaint
    {
        $query = Complaint::query()
            ->where('id', $complaintId)
            ->where('is_public', true)
            ->whereIn('status', self::ALLOWED_STATUSES)
            ->with([
                'category:id,name',
                'currentDepartment:id,name,ministry_id',
                'currentDepartment.ministry:id,name',
                'attachments',
            ]);

        if ($user) {
            $query->with(['likes' => function ($q) use ($user) {
                $q->where('user_id', $user->id);
            }]);
        }

        $complaint = $query->first();

        if (! $complaint) {
            throw new NotFoundHttpException('البلاغ العام غير موجود أو غير متاح للعرض.');
        }

        return $complaint;
    }

    /**
     * Retrieve lightweight public complaints for map visualization with bounding box filtering.
     *
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Complaint>
     */
    public function getMapComplaints(array $filters = []): Collection
    {
        $query = Complaint::query()
            ->where('is_public', true)
            ->whereIn('status', self::ALLOWED_STATUSES)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('latitude', '!=', 0)
            ->where('longitude', '!=', 0);

        // Bounding Box filtering (min_lat, min_lng, max_lat, max_lng)
        if (! empty($filters['bbox'])) {
            $bboxCoords = explode(',', (string) $filters['bbox']);
            if (count($bboxCoords) === 4) {
                $minLat = (float) $bboxCoords[0];
                $minLng = (float) $bboxCoords[1];
                $maxLat = (float) $bboxCoords[2];
                $maxLng = (float) $bboxCoords[3];

                $query->whereBetween('latitude', [$minLat, $maxLat])
                      ->whereBetween('longitude', [$minLng, $maxLng]);
            }
        } elseif (
            isset($filters['min_lat'], $filters['max_lat'], $filters['min_lng'], $filters['max_lng'])
        ) {
            $query->whereBetween('latitude', [(float) $filters['min_lat'], (float) $filters['max_lat']])
                  ->whereBetween('longitude', [(float) $filters['min_lng'], (float) $filters['max_lng']]);
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', (int) $filters['category_id']);
        }

        if (! empty($filters['status']) && in_array($filters['status'], self::ALLOWED_STATUSES, true)) {
            $query->where('status', $filters['status']);
        }

        $maxPins = (int) config('balagh.community.max_map_pins', 150);

        return $query->select([
            'id',
            'title',
            'status',
            'priority',
            'latitude',
            'longitude',
            'likes_count',
            'category_id',
            'current_department_id',
            'created_at',
        ])
        ->with(['category:id,name'])
        ->orderByDesc('likes_count')
        ->limit($maxPins)
        ->get();
    }

    /**
     * Add like to a public complaint by authenticated citizen with row-level locking.
     *
     * @return array{liked: bool, likes_count: int, already_liked: bool}
     */
    public function like(int $complaintId, User $user): array
    {
        return DB::transaction(function () use ($complaintId, $user) {
            /** @var Complaint|null $complaint */
            $complaint = Complaint::where('id', $complaintId)->lockForUpdate()->first();

            if (! $complaint || ! $complaint->is_public || ! in_array($complaint->status, self::ALLOWED_STATUSES, true)) {
                throw new BadRequestHttpException('لا يمكن التفاعل مع هذا البلاغ لأنه غير عام أو غير معتمد.');
            }

            // Check if already liked
            $existing = ComplaintLike::where('complaint_id', $complaint->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return [
                    'liked' => true,
                    'likes_count' => (int) $complaint->likes_count,
                    'already_liked' => true,
                ];
            }

            // Create like record
            ComplaintLike::create([
                'complaint_id' => $complaint->id,
                'user_id' => $user->id,
            ]);

            // Atomic increment
            $complaint->increment('likes_count');
            $complaint->refresh();

            // Trigger engagement check (trending threshold notification without priority elevation)
            $this->engagementService->checkAndTriggerTrendingAlert($complaint, $user->id);

            return [
                'liked' => true,
                'likes_count' => (int) $complaint->likes_count,
                'already_liked' => false,
            ];
        });
    }

    /**
     * Remove like from a public complaint with non-negative counter guarantee.
     *
     * @return array{liked: bool, likes_count: int, unliked: bool}
     */
    public function unlike(int $complaintId, User $user): array
    {
        return DB::transaction(function () use ($complaintId, $user) {
            /** @var Complaint|null $complaint */
            $complaint = Complaint::where('id', $complaintId)->lockForUpdate()->first();

            if (! $complaint) {
                throw new NotFoundHttpException('البلاغ غير موجود.');
            }

            $deleted = ComplaintLike::where('complaint_id', $complaint->id)
                ->where('user_id', $user->id)
                ->delete();

            if ($deleted > 0) {
                // Ensure non-negative counter atomically across all database engines (SQLite, MySQL, Postgres)
                DB::table('complaints')
                    ->where('id', $complaint->id)
                    ->update([
                        'likes_count' => DB::raw('CASE WHEN likes_count > 0 THEN likes_count - 1 ELSE 0 END'),
                    ]);

                $complaint->refresh();

                return [
                    'liked' => false,
                    'likes_count' => (int) $complaint->likes_count,
                    'unliked' => true,
                ];
            }

            return [
                'liked' => false,
                'likes_count' => (int) $complaint->likes_count,
                'unliked' => false,
            ];
        });
    }
}
