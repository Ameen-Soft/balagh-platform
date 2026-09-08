<?php

namespace App\Policies;

use App\Models\Complaint;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ComplaintPolicy
{
    use HandlesAuthorization;

    /**
     * Super Admin bypass for all abilities.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('complaints.view') || $user->hasRole('Citizen');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Complaint $complaint): bool
    {
        // Citizen can view their own complaint
        if ($complaint->citizen_id === $user->id) {
            return true;
        }

        // Ministry Admin / Staff can view complaints routed to their ministry
        $userMinistryId = $user->department?->ministry_id;
        $complaintMinistryId = $complaint->currentDepartment?->ministry_id;
        if ($userMinistryId && $complaintMinistryId === $userMinistryId) {
            return $user->hasPermission('complaints.view');
        }

        // Field Worker can view complaints assigned to them
        if ($user->hasRole('Field Worker')) {
            return $complaint->fieldAssignments()->where('worker_id', $user->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('complaints.create') || $user->hasRole('Citizen');
    }

    /**
     * Determine whether the user can update the model / change status.
     */
    public function update(User $user, Complaint $complaint): bool
    {
        $userMinistryId = $user->department?->ministry_id;
        $complaintMinistryId = $complaint->currentDepartment?->ministry_id;

        if ($userMinistryId && $complaintMinistryId === $userMinistryId) {
            return $user->hasPermission('complaints.review');
        }

        return false;
    }

    /**
     * Determine whether the user can transfer the complaint.
     */
    public function transfer(User $user, Complaint $complaint): bool
    {
        $userMinistryId = $user->department?->ministry_id;
        $complaintMinistryId = $complaint->currentDepartment?->ministry_id;

        if ($userMinistryId && $complaintMinistryId === $userMinistryId) {
            return $user->hasPermission('complaints.transfer');
        }

        return false;
    }

    /**
     * Determine whether the user can assign a field worker to the complaint.
     */
    public function assignFieldWorker(User $user, Complaint $complaint): bool
    {
        $userMinistryId = $user->department?->ministry_id;
        $complaintMinistryId = $complaint->currentDepartment?->ministry_id;

        if ($userMinistryId && $complaintMinistryId === $userMinistryId) {
            return $user->hasPermission('tasks.assign');
        }

        return false;
    }

    /**
     * Determine whether the user can reject the complaint.
     */
    public function reject(User $user, Complaint $complaint): bool
    {
        $userMinistryId = $user->department?->ministry_id;
        $complaintMinistryId = $complaint->currentDepartment?->ministry_id;

        if ($userMinistryId && $complaintMinistryId === $userMinistryId) {
            return $user->hasPermission('complaints.reject');
        }

        return false;
    }

    /**
     * Determine whether the user can close the complaint.
     */
    public function close(User $user, Complaint $complaint): bool
    {
        // Citizen can close if status is resolved
        if ($complaint->citizen_id === $user->id && $complaint->status === 'resolved') {
            return true;
        }

        $userMinistryId = $user->department?->ministry_id;
        $complaintMinistryId = $complaint->currentDepartment?->ministry_id;
        if ($userMinistryId && $complaintMinistryId === $userMinistryId) {
            return $user->hasPermission('complaints.close');
        }

        return false;
    }
}
