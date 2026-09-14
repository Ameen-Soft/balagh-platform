<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\ComplaintAttachment;
use App\Models\ComplaintTimeline;
use App\Models\FieldAssignment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FieldWorkService
{
    public function __construct(
        protected GeoLocationService $geoService,
        protected FileUploadService $fileUploadService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Assign a complaint to a field worker.
     *
     * @throws ValidationException
     */
    public function assignWorker(Complaint $complaint, int $workerId, ?string $notes, User $assignedBy): FieldAssignment
    {
        $worker = User::find($workerId);
        if (! $worker || ! $worker->hasRole('Field Worker')) {
            throw ValidationException::withMessages([
                'user_id' => ['المستخدم المحدد ليس موظفاً ميدانياً نشطاً.'],
            ]);
        }

        $assignment = DB::transaction(function () use ($complaint, $worker, $notes, $assignedBy) {
            // Cancel any previously active assignment for this complaint
            FieldAssignment::where('complaint_id', $complaint->id)
                ->whereIn('status', ['pending', 'accepted', 'in_progress'])
                ->update(['status' => 'failed']);

            // Create new assignment
            $assignment = FieldAssignment::create([
                'complaint_id' => $complaint->id,
                'worker_id' => $worker->id,
                'assigned_by' => $assignedBy->id,
                'status' => 'pending',
                'notes' => $notes,
            ]);

            // Update complaint status to 'assigned'
            $complaint->update(['status' => 'assigned']);

            // Add timeline entry
            ComplaintTimeline::create([
                'complaint_id' => $complaint->id,
                'event_type' => 'assigned',
                'description' => "تم إسناد البلاغ للموظف الميداني [{$worker->name}].",
                'old_value' => null,
                'new_value' => (string) $worker->id,
                'performed_by' => $assignedBy->id,
            ]);

            return $assignment;
        });

        // Side-effect notification outside transaction
        $this->notificationService->send(
            userId: $worker->id,
            title: 'مهمة ميدانية جديدة',
            body: "تم إسناد مهمة معاينة للبلاغ '{$complaint->title}' إليك.",
            type: 'task_assigned',
            data: ['assignment_id' => $assignment->id, 'complaint_id' => $complaint->id]
        );

        return $assignment->load(['complaint', 'worker', 'assignedBy']);
    }

    /**
     * Accept a field assignment.
     *
     * @throws ValidationException
     */
    public function acceptAssignment(FieldAssignment $assignment, User $worker): FieldAssignment
    {
        if ($assignment->worker_id !== $worker->id) {
            throw ValidationException::withMessages([
                'assignment' => ['غير مصرح لك بقبول هذه المهمة الميدانية.'],
            ]);
        }

        if (! in_array($assignment->status, ['pending', 'assigned'])) {
            throw ValidationException::withMessages([
                'assignment' => ['لا يمكن قبول هذه المهمة لأن حالتها الحالية ليست قيد الانتظار.'],
            ]);
        }

        $oldStatus = $assignment->status;

        return DB::transaction(function () use ($assignment, $worker, $oldStatus) {
            $assignment->update([
                'status' => 'accepted',
            ]);

            $complaint = $assignment->complaint;
            if ($complaint) {
                ComplaintTimeline::create([
                    'complaint_id' => $complaint->id,
                    'event_type' => 'status_changed',
                    'description' => "قبل الموظف الميداني [{$worker->name}] المهمة وهو في الطريق إلى الموقع.",
                    'old_value' => $oldStatus,
                    'new_value' => 'accepted',
                    'performed_by' => $worker->id,
                ]);
            }

            return $assignment->fresh(['complaint', 'worker']);
        });
    }

    /**
     * Start a field assignment.
     *
     * @throws ValidationException
     */
    public function startAssignment(FieldAssignment $assignment, User $worker): FieldAssignment
    {
        if ($assignment->worker_id !== $worker->id) {
            throw ValidationException::withMessages([
                'assignment' => ['غير مصرح لك بالبدء في هذه المهمة الميدانية.'],
            ]);
        }

        if (! in_array($assignment->status, ['pending', 'assigned', 'accepted'])) {
            throw ValidationException::withMessages([
                'assignment' => ['لا يمكن بدء هذه المهمة الميدانية في حالتها الحالية.'],
            ]);
        }

        $oldStatus = $assignment->status;

        return DB::transaction(function () use ($assignment, $worker, $oldStatus) {
            $assignment->update([
                'status' => 'in_progress',
                'started_at' => now(),
            ]);

            $complaint = $assignment->complaint;
            if ($complaint) {
                $complaint->update(['status' => 'in_progress']);

                ComplaintTimeline::create([
                    'complaint_id' => $complaint->id,
                    'event_type' => 'status_changed',
                    'description' => "بدأ الموظف الميداني [{$worker->name}] في تنفيذ المعاينة الميدانية.",
                    'old_value' => $oldStatus,
                    'new_value' => 'in_progress',
                    'performed_by' => $worker->id,
                ]);
            }

            return $assignment->fresh(['complaint', 'worker']);
        });
    }

    /**
     * Verify worker geolocation proximity against complaint coordinates.
     *
     * @throws ValidationException
     */
    public function verifyWorkerLocation(
        FieldAssignment $assignment,
        float $latitude,
        float $longitude,
        User $worker
    ): array {
        if ($assignment->worker_id !== $worker->id) {
            throw ValidationException::withMessages([
                'assignment' => ['غير مصرح لك بإجراء التحقق من الموقع لهذه المهمة.'],
            ]);
        }

        $complaint = $assignment->complaint;
        if (! $complaint) {
            throw ValidationException::withMessages([
                'assignment' => ['البلاغ المرتبط بهذه المهمة غير موجود.'],
            ]);
        }

        $allowedRadius = (float) config('balagh.geo.field_worker_radius_meters', 500.0);
        $distance = $this->geoService->calculateDistance(
            $latitude,
            $longitude,
            (float) $complaint->latitude,
            (float) $complaint->longitude
        );

        $isWithinRange = $distance <= $allowedRadius;

        return [
            'assignment_id' => $assignment->id,
            'complaint_id' => $complaint->id,
            'worker_location' => [
                'latitude' => $latitude,
                'longitude' => $longitude,
            ],
            'complaint_location' => [
                'latitude' => (float) $complaint->latitude,
                'longitude' => (float) $complaint->longitude,
            ],
            'distance_meters' => $distance,
            'allowed_radius_meters' => $allowedRadius,
            'is_within_range' => $isWithinRange,
            'message' => $isWithinRange
                ? 'الموظف الميداني متواجد داخل النطاق الجغرافي المحدد للبلاغ.'
                : "الموظف الميداني يبعد {$distance} متراً عن موقع البلاغ (الحد الأقصى المسموح: {$allowedRadius} متراً).",
        ];
    }

    /**
     * Complete a field assignment with report, geolocation verification, evidence uploads, and atomic resolution.
     *
     * @param  array<\Illuminate\Http\UploadedFile>|null  $evidenceFiles
     *
     * @throws ValidationException
     */
    public function completeAssignment(
        FieldAssignment $assignment,
        string $report,
        float $latitude,
        float $longitude,
        ?array $evidenceFiles,
        User $worker
    ): FieldAssignment {
        if ($assignment->worker_id !== $worker->id) {
            throw ValidationException::withMessages([
                'assignment' => ['غير مصرح لك بإتمام هذه المهمة.'],
            ]);
        }

        $complaint = $assignment->complaint;
        if (! $complaint) {
            throw ValidationException::withMessages([
                'assignment' => ['البلاغ المرتبط بهذه المهمة غير موجود.'],
            ]);
        }

        // 1. Geofencing check: Worker must be physically near the incident site
        $allowedRadius = (float) config('balagh.geo.field_worker_radius_meters', 500.0);
        $distance = $this->geoService->calculateDistance(
            $latitude,
            $longitude,
            (float) $complaint->latitude,
            (float) $complaint->longitude
        );

        if ($distance > $allowedRadius) {
            throw ValidationException::withMessages([
                'coordinates' => [
                    "موقعك الحالي يبعد {$distance} متراً عن موقع البلاغ، وهو ما يتجاوز النطاق المسموح به ({$allowedRadius} متراً) لإتمام المعاينة الميدانية.",
                ],
            ]);
        }

        // 2. Atomic Database Transaction
        DB::transaction(function () use ($assignment, $complaint, $report, $latitude, $longitude, $evidenceFiles, $worker) {
            // Update assignment status
            $assignment->update([
                'status' => 'completed',
                'completed_at' => now(),
                'notes' => $report,
            ]);

            // Upload and attach evidence
            if (! empty($evidenceFiles)) {
                $folder = config('balagh.storage.evidence_folder', 'field_evidence');
                foreach ($evidenceFiles as $file) {
                    $meta = $this->fileUploadService->upload($file, $folder);
                    ComplaintAttachment::create([
                        'complaint_id' => $complaint->id,
                        'file_path' => $meta['file_path'],
                        'file_type' => $meta['file_type'],
                        'captured_latitude' => $latitude,
                        'captured_longitude' => $longitude,
                        'uploaded_by' => $worker->id,
                        'type' => 'after',
                    ]);
                }
            }

            // Update complaint status to resolved
            $complaint->update(['status' => 'resolved']);

            // Record in timeline
            ComplaintTimeline::create([
                'complaint_id' => $complaint->id,
                'event_type' => 'resolved',
                'description' => "أتم الباحث الميداني المعالجة بنجاح. تقرير الإنجاز: {$report}",
                'old_value' => 'in_progress',
                'new_value' => 'resolved',
                'performed_by' => $worker->id,
            ]);
        });

        // 3. Post-Commit Notification to citizen
        $this->notificationService->send(
            userId: $complaint->citizen_id,
            title: 'تم حل البلاغ بنجاح',
            body: "تمت معالجة بلاغك '{$complaint->title}' بواسطة الفريق الميداني بنجاح.",
            type: 'complaint_resolved',
            data: ['complaint_id' => $complaint->id]
        );

        return $assignment->fresh(['complaint.attachments', 'worker']);
    }

    /**
     * List assignments for a field worker or department.
     */
    public function listAssignments(User $user, ?string $status = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = FieldAssignment::with(['complaint.category', 'complaint.currentDepartment', 'worker', 'assignedBy']);

        if ($user->hasRole('Field Worker') && ! $user->hasRole('Super Admin')) {
            $query->where('worker_id', $user->id);
        } elseif ($user->department_id && ! $user->hasRole('Super Admin')) {
            $query->whereHas('complaint', function ($q) use ($user) {
                $q->where('current_department_id', $user->department_id);
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        return $query->latest()->paginate($perPage);
    }
}
