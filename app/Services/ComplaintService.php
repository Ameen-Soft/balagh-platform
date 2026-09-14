<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\ComplaintAttachment;
use App\Models\ComplaintTimeline;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ComplaintService
{
    public function __construct(
        protected ValidationService $validationService,
        protected DuplicateDetectionService $duplicateService,
        protected RoutingService $routingService,
        protected FileUploadService $fileUploadService,
        protected NotificationService $notificationService
    ) {}

    /**
     * File a new complaint with business validation, duplicate detection, automated routing,
     * atomic DB transaction, and post-commit notifications.
     *
     * @param  array<string, mixed>  $data
     * @param  array<\Illuminate\Http\UploadedFile>|null  $uploadedFiles
     */
    public function createComplaint(array $data, User $citizen, ?array $uploadedFiles = null): Complaint
    {
        // 1. Business-level validations
        $this->validationService->validateComplaintBusinessRules($data, $citizen);

        // 2. Intelligent Duplicate Detection
        $duplicateResult = $this->duplicateService->check([
            'category_id' => (int) $data['category_id'],
            'latitude' => (float) $data['latitude'],
            'longitude' => (float) $data['longitude'],
            'title' => (string) $data['title'],
            'description' => (string) $data['description'],
        ]);

        // 3. Automated Routing to Department and Ministry
        $routeResult = $this->routingService->route((int) $data['category_id']);

        // 4. Atomic Database Transaction (Business State Only)
        $complaint = DB::transaction(function () use ($data, $citizen, $duplicateResult, $routeResult, $uploadedFiles) {
            $complaint = Complaint::create([
                'citizen_id' => $citizen->id,
                'category_id' => $data['category_id'],
                'current_department_id' => $routeResult['department_id'],
                'duplicate_of_id' => $duplicateResult['is_duplicate'] ? $duplicateResult['matched_complaint_id'] : null,
                'title' => $data['title'],
                'description' => $data['description'],
                'status' => 'new',
                'priority' => $data['priority'] ?? 'medium',
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
            ]);

            // Save attachments if present
            if (! empty($uploadedFiles)) {
                $folder = config('balagh.storage.complaints_folder', 'complaints');
                foreach ($uploadedFiles as $file) {
                    $meta = $this->fileUploadService->upload($file, $folder);
                    ComplaintAttachment::create([
                        'complaint_id' => $complaint->id,
                        'file_path' => $meta['file_path'],
                        'file_type' => $meta['file_type'],
                        'captured_latitude' => $data['latitude'],
                        'captured_longitude' => $data['longitude'],
                        'uploaded_by' => $citizen->id,
                        'type' => 'before',
                    ]);
                }
            }

            // Record initial timeline entry
            $initialNotes = 'تم تقديم البلاغ وتوجيهه آلياً للقسم المختص.';
            if ($duplicateResult['is_duplicate']) {
                $initialNotes .= " (تنبيه: تم رصد تشابه بنسبة {$duplicateResult['match_score']}% مع البلاغ رقم #{$duplicateResult['matched_complaint_id']})";
            }

            ComplaintTimeline::create([
                'complaint_id' => $complaint->id,
                'event_type' => 'created',
                'description' => $initialNotes,
                'old_value' => null,
                'new_value' => 'new',
                'performed_by' => $citizen->id,
            ]);

            return $complaint;
        });

        // 5. Post-Commit Side Effects: Send Notification outside the transaction
        $this->notificationService->send(
            userId: $citizen->id,
            title: 'تم استلام بلاغك بنجاح',
            body: "تم تسجيل بلاغك بعنوان '{$complaint->title}' وتوجيهه للإدارة المعنية لمراجعته.",
            type: 'complaint_created',
            data: ['complaint_id' => $complaint->id]
        );

        return $complaint->load([
            'citizen',
            'category',
            'currentDepartment.ministry',
            'attachments',
            'timeline',
        ]);
    }

    /**
     * Update status of a complaint with atomic timeline recording and side-effect notification.
     */
    public function updateStatus(Complaint $complaint, string $newStatus, ?string $notes, User $performer): Complaint
    {
        $oldStatus = $complaint->status;

        DB::transaction(function () use ($complaint, $newStatus, $oldStatus, $notes, $performer) {
            $complaint->update(['status' => $newStatus]);

            ComplaintTimeline::create([
                'complaint_id' => $complaint->id,
                'event_type' => 'status_changed',
                'description' => $notes ?? "تم تغيير حالة البلاغ من [{$oldStatus}] إلى [{$newStatus}].",
                'old_value' => $oldStatus,
                'new_value' => $newStatus,
                'performed_by' => $performer->id,
            ]);
        });

        // Post-Commit Notification
        $this->notificationService->send(
            userId: $complaint->citizen_id,
            title: 'تحديث حالة البلاغ',
            body: "تم تغيير حالة بلاغك '{$complaint->title}' إلى: {$newStatus}.",
            type: 'complaint_status_changed',
            data: [
                'complaint_id' => $complaint->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]
        );

        return $complaint->fresh(['timeline', 'currentDepartment', 'category']);
    }

    /**
     * Retrieve full details of a complaint.
     */
    public function getComplaintDetails(Complaint $complaint): Complaint
    {
        return $complaint->load([
            'citizen',
            'category',
            'currentDepartment.ministry',
            'attachments.uploader',
            'timeline.performer',
            'transfers.fromDepartment',
            'transfers.toDepartment',
            'transfers.transferredBy',
            'fieldAssignments.worker',
            'fieldAssignments.assignedBy',
            'duplicates',
            'duplicateOf',
        ]);
    }

    /**
     * List complaints with filtering, sorting, pagination, and RBAC scope.
     *
     * @param  array<string, mixed>  $filters
     */
    public function listComplaints(array $filters, User $user): LengthAwarePaginator
    {
        $query = Complaint::with(['citizen', 'category', 'currentDepartment.ministry', 'attachments']);

        // Data Scope based on Roles
        if ($user->hasRole('Citizen') && ! $user->hasRole('Super Admin')) {
            $query->where('citizen_id', $user->id);
        } elseif ($user->hasRole('Field Worker') && ! $user->hasRole('Super Admin')) {
            $query->whereHas('fieldAssignments', function ($q) use ($user) {
                $q->where('worker_id', $user->id);
            });
        } elseif ($user->department_id && ! $user->hasRole('Super Admin')) {
            $userMinistryId = $user->department?->ministry_id;
            if ($userMinistryId) {
                $query->whereHas('currentDepartment', function ($q) use ($userMinistryId) {
                    $q->where('ministry_id', $userMinistryId);
                });
            }
        }

        // Apply filters
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['department_id'])) {
            $query->where('current_department_id', $filters['department_id']);
        }

        if (! empty($filters['ministry_id'])) {
            $query->whereHas('currentDepartment', function ($q) use ($filters) {
                $q->where('ministry_id', $filters['ministry_id']);
            });
        }

        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (! empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                    ->orWhere('description', 'like', $search);
            });
        }

        if (! empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (! empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);

        $perPage = (int) ($filters['per_page'] ?? 15);

        return $query->paginate($perPage);
    }
}
