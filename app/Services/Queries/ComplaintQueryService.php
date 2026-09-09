<?php

namespace App\Services\Queries;

use App\Models\Complaint;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ComplaintQueryService
{
    /**
     * Search, filter, and paginate complaints for Admin and Ministry Dashboards.
     */
    public function paginateComplaints(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Complaint::with([
            'citizen:id,name,phone',
            'category:id,name',
            'currentDepartment:id,name,ministry_id',
            'currentDepartment.ministry:id,name',
            'fieldAssignments.worker:id,name',
        ]);

        // Filter by Ministry ID
        if (!empty($filters['ministry_id'])) {
            $ministryId = $filters['ministry_id'];
            $query->whereHas('currentDepartment', fn ($q) => $q->where('ministry_id', $ministryId));
        }

        // Filter by Department ID
        if (!empty($filters['department_id'])) {
            $query->where('current_department_id', $filters['department_id']);
        }

        // Filter by Status
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        // Filter by Priority
        if (!empty($filters['priority']) && $filters['priority'] !== 'all') {
            $query->where('priority', $filters['priority']);
        }

        // Filter by Category ID
        if (!empty($filters['category_id']) && $filters['category_id'] !== 'all') {
            $query->where('category_id', $filters['category_id']);
        }

        // Date range filter
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Text Search
        if (!empty($filters['search'])) {
            $rawTerm = trim($filters['search']);
            $searchTerm = '%' . $rawTerm . '%';
            $numericId = preg_replace('/[^0-9]/', '', $rawTerm);

            $query->where(function ($q) use ($searchTerm, $numericId) {
                $q->where('title', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm);

                if (!empty($numericId)) {
                    $q->orWhere('id', (int) $numericId);
                }

                $q->orWhereHas('citizen', function ($sq) use ($searchTerm) {
                    $sq->where('name', 'like', $searchTerm)
                        ->orWhere('phone', 'like', $searchTerm);
                });
            });
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';
        $allowedSorts = ['created_at', 'updated_at', 'priority', 'status', 'title'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        return $query->paginate($perPage);
    }

    /**
     * Get complete complaint dossier for administrative review.
     */
    public function getComplaintDossier(int|string $id): Complaint
    {
        return Complaint::with([
            'citizen',
            'category',
            'currentDepartment.ministry',
            'attachments',
            'timelines.actor',
            'fieldAssignments.worker',
            'fieldAssignments.evidence',
            'transfers.fromDepartment',
            'transfers.toDepartment',
            'transfers.transferredByUser',
        ])->findOrFail($id);
    }

    /**
     * Get complaints for Public Transparency portal (masked citizen identity).
     */
    public function paginatePublicComplaints(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Complaint::with([
            'category:id,name',
            'currentDepartment:id,name,ministry_id',
            'currentDepartment.ministry:id,name',
            'attachments',
        ])
        ->whereIn('status', ['resolved', 'closed', 'in_progress', 'under_review']);

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $rawTerm = trim($filters['search']);
            $search = '%' . $rawTerm . '%';
            $numericId = preg_replace('/[^0-9]/', '', $rawTerm);

            $query->where(function ($q) use ($search, $numericId) {
                $q->where('title', 'like', $search)
                    ->orWhere('description', 'like', $search);

                if (!empty($numericId)) {
                    $q->orWhere('id', (int) $numericId);
                }
            });
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get public complaint detail with masked sensitive citizen information.
     */
    public function getPublicComplaintDetail(int|string $id): Complaint
    {
        $complaint = Complaint::with([
            'category:id,name',
            'currentDepartment.ministry:id,name',
            'attachments',
            'timelines' => fn ($q) => $q->latest(),
        ])->findOrFail($id);

        // Mask citizen sensitive data in representation
        if ($complaint->relationLoaded('citizen')) {
            $complaint->unsetRelation('citizen');
        }

        return $complaint;
    }
}
