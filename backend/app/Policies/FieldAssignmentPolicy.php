<?php

namespace App\Policies;

use App\Models\FieldAssignment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FieldAssignmentPolicy
{
    use HandlesAuthorization;

    /**
     * Super Admin bypass for monitoring abilities only.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('Super Admin') && in_array($ability, ['viewAny', 'view'])) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any field assignments.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('tasks.view');
    }

    /**
     * Determine whether the user can view the field assignment.
     */
    public function view(User $user, FieldAssignment $assignment): bool
    {
        // Assigned field worker
        if ($assignment->worker_id === $user->id) {
            return true;
        }

        // Ministry admin of the complaint
        $userMinistryId = $user->department?->ministry_id;
        $complaintMinistryId = $assignment->complaint?->currentDepartment?->ministry_id;
        if ($userMinistryId && $complaintMinistryId === $userMinistryId) {
            return $user->hasPermission('tasks.view');
        }

        return false;
    }

    /**
     * Determine whether the field worker can accept the assignment.
     */
    public function accept(User $user, FieldAssignment $assignment): bool
    {
        return $assignment->worker_id === $user->id
            && in_array($assignment->status, ['assigned', 'pending'])
            && $user->hasPermission('tasks.update_status');
    }

    /**
     * Determine whether the field worker can start the assignment.
     */
    public function start(User $user, FieldAssignment $assignment): bool
    {
        return $assignment->worker_id === $user->id
            && in_array($assignment->status, ['assigned', 'pending', 'accepted'])
            && $user->hasPermission('tasks.update_status');
    }

    /**
     * Determine whether the field worker can verify their location for the assignment.
     */
    public function verifyLocation(User $user, FieldAssignment $assignment): bool
    {
        return $assignment->worker_id === $user->id
            && in_array($assignment->status, ['assigned', 'pending', 'accepted', 'in_progress'])
            && $user->hasPermission('tasks.update_status');
    }

    /**
     * Determine whether the field worker can complete the assignment.
     */
    public function complete(User $user, FieldAssignment $assignment): bool
    {
        return $assignment->worker_id === $user->id
            && $assignment->status === 'in_progress'
            && $user->hasPermission('tasks.update_status');
    }
}
