<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\ComplaintTimeline;
use App\Models\ComplaintTransfer;
use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransferService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Transfer a complaint to another department with atomic history tracking.
     *
     * @throws ValidationException
     */
    public function transferComplaint(Complaint $complaint, int $toDepartmentId, string $reason, User $performer): ComplaintTransfer
    {
        $toDepartment = Department::with('ministry')->find($toDepartmentId);
        if (! $toDepartment) {
            throw ValidationException::withMessages([
                'to_department_id' => ['الإدارة المحول إليها غير متوفرة في النظام.'],
            ]);
        }

        $fromDepartmentId = $complaint->current_department_id;
        $fromDepartment = Department::find($fromDepartmentId);

        if ($fromDepartmentId === $toDepartmentId) {
            throw ValidationException::withMessages([
                'to_department_id' => ['لا يمكن تحويل البلاغ لنفس الإدارة المسند إليها حالياً.'],
            ]);
        }

        $transfer = DB::transaction(function () use ($complaint, $fromDepartmentId, $toDepartmentId, $fromDepartment, $toDepartment, $reason, $performer) {
            // 1. Create transfer record
            $transfer = ComplaintTransfer::create([
                'complaint_id' => $complaint->id,
                'from_department_id' => $fromDepartmentId,
                'to_department_id' => $toDepartmentId,
                'reason' => $reason,
                'transferred_by' => $performer->id,
            ]);

            // 2. Update complaint's current department and state
            $complaint->update([
                'current_department_id' => $toDepartmentId,
                'status' => 'under_review',
            ]);

            // 3. Record timeline entry
            $fromName = $fromDepartment?->name ?? 'غير محدد';
            $toName = $toDepartment->name;

            ComplaintTimeline::create([
                'complaint_id' => $complaint->id,
                'event_type' => 'transferred',
                'description' => "تم تحويل البلاغ من [{$fromName}] إلى [{$toName}]. السبب: {$reason}",
                'old_value' => (string) $fromDepartmentId,
                'new_value' => (string) $toDepartmentId,
                'performed_by' => $performer->id,
            ]);

            return $transfer;
        });

        // 4. Post-Commit Notifications (Side effects outside transaction)
        $this->notificationService->send(
            userId: $complaint->citizen_id,
            title: 'إعادة توجيه البلاغ',
            body: "تم تحويل بلاغك '{$complaint->title}' إلى {$toDepartment->name} لمتابعة الإجراءات.",
            type: 'complaint_transferred',
            data: ['complaint_id' => $complaint->id, 'to_department' => $toDepartment->name]
        );

        return $transfer->load(['complaint', 'fromDepartment', 'toDepartment', 'transferredBy']);
    }
}
