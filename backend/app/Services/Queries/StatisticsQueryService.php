<?php

namespace App\Services\Queries;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\Department;
use App\Models\FieldAssignment;
use App\Models\Ministry;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StatisticsQueryService
{
    /**
     * Get global platform overview statistics for Super Admin.
     */
    public function getGlobalOverview(): array
    {
        $totalComplaints = Complaint::count();
        $resolvedCount = Complaint::where('status', 'resolved')->count();
        $inProgressCount = Complaint::where('status', 'in_progress')->count();
        $underReviewCount = Complaint::where('status', 'under_review')->count();
        $submittedCount = Complaint::where('status', 'submitted')->count();
        $rejectedCount = Complaint::where('status', 'rejected')->count();
        $closedCount = Complaint::where('status', 'closed')->count();

        $resolutionRate = $totalComplaints > 0
            ? round((($resolvedCount + $closedCount) / $totalComplaints) * 100, 1)
            : 0;

        $totalCitizens = User::whereHas('roles', fn ($q) => $q->where('name', 'Citizen'))->count();
        $totalFieldWorkers = User::whereHas('roles', fn ($q) => $q->where('name', 'Field Worker'))->count();
        $totalMinistries = Ministry::count();
        $totalDepartments = Department::count();
        $totalProjects = Project::count();
        $activeProjects = Project::where('status', 'in_progress')->count();

        return [
            'total_complaints' => $totalComplaints,
            'resolved_complaints' => $resolvedCount,
            'in_progress_complaints' => $inProgressCount,
            'under_review_complaints' => $underReviewCount,
            'submitted_complaints' => $submittedCount,
            'rejected_complaints' => $rejectedCount,
            'closed_complaints' => $closedCount,
            'resolution_rate' => $resolutionRate,
            'total_citizens' => $totalCitizens,
            'total_field_workers' => $totalFieldWorkers,
            'total_ministries' => $totalMinistries,
            'total_departments' => $totalDepartments,
            'total_projects' => $totalProjects,
            'active_projects' => $activeProjects,
        ];
    }

    /**
     * Get statistics scoped to a specific department or ministry for Ministry Admin.
     */
    public function getDepartmentOverview(int $departmentId): array
    {
        $dept = Department::with('ministry')->find($departmentId);
        $ministryId = $dept?->ministry_id;

        // Query complaints that belong to this ministry
        $baseQuery = Complaint::whereHas('currentDepartment', function ($q) use ($ministryId, $departmentId) {
            if ($ministryId) {
                $q->where('ministry_id', $ministryId);
            } else {
                $q->where('id', $departmentId);
            }
        });

        $total = (clone $baseQuery)->count();
        $submitted = (clone $baseQuery)->where('status', 'submitted')->count();
        $underReview = (clone $baseQuery)->where('status', 'under_review')->count();
        $inProgress = (clone $baseQuery)->where('status', 'in_progress')->count();
        $resolved = (clone $baseQuery)->where('status', 'resolved')->count();
        $rejected = (clone $baseQuery)->where('status', 'rejected')->count();
        $closed = (clone $baseQuery)->where('status', 'closed')->count();
        $urgent = (clone $baseQuery)->where('priority', 'urgent')->whereNotIn('status', ['resolved', 'closed', 'rejected'])->count();

        $resolutionRate = $total > 0
            ? round((($resolved + $closed) / $total) * 100, 1)
            : 0;

        // Active field assignments for this department
        $activeAssignments = FieldAssignment::whereHas('complaint', function ($q) use ($ministryId, $departmentId) {
            $q->whereHas('currentDepartment', function ($sq) use ($ministryId, $departmentId) {
                if ($ministryId) {
                    $sq->where('ministry_id', $ministryId);
                } else {
                    $sq->where('id', $departmentId);
                }
            });
        })->whereIn('status', ['assigned', 'in_progress'])->count();

        return [
            'department' => $dept,
            'total_complaints' => $total,
            'submitted' => $submitted,
            'under_review' => $underReview,
            'in_progress' => $inProgress,
            'resolved' => $resolved,
            'rejected' => $rejected,
            'closed' => $closed,
            'urgent' => $urgent,
            'resolution_rate' => $resolutionRate,
            'active_assignments' => $activeAssignments,
        ];
    }

    /**
     * Get complaints count distributed by status.
     */
    public function getComplaintsByStatus(?int $departmentId = null): array
    {
        $query = Complaint::query();

        if ($departmentId) {
            $query->where('current_department_id', $departmentId);
        }

        $results = $query->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $statuses = ['submitted', 'under_review', 'in_progress', 'resolved', 'rejected', 'closed'];
        $formatted = [];
        foreach ($statuses as $status) {
            $formatted[$status] = $results[$status] ?? 0;
        }

        return $formatted;
    }

    /**
     * Get complaints count distributed by category.
     */
    public function getComplaintsByCategory(?int $departmentId = null, int $limit = 6): array
    {
        $query = Complaint::query()
            ->join('categories', 'complaints.category_id', '=', 'categories.id');

        if ($departmentId) {
            $query->where('complaints.current_department_id', $departmentId);
        }

        return $query->select('categories.name', DB::raw('count(*) as count'))
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('count')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Public transparency statistics for the Landing Page and public portals.
     */
    public function getPublicTransparencyStats(): array
    {
        $totalComplaints = Complaint::count();
        $resolvedCount = Complaint::whereIn('status', ['resolved', 'closed'])->count();
        $totalCitizens = User::whereHas('roles', fn ($q) => $q->where('name', 'Citizen'))->count();
        $totalProjects = Project::count();
        $completedProjects = Project::where('status', 'completed')->count();

        $resolutionRate = $totalComplaints > 0
            ? round(($resolvedCount / $totalComplaints) * 100, 1)
            : 0;

        return [
            'total_complaints' => $totalComplaints,
            'resolved_complaints' => $resolvedCount,
            'resolution_rate' => $resolutionRate,
            'total_citizens' => $totalCitizens,
            'total_projects' => $totalProjects,
            'completed_projects' => $completedProjects,
        ];
    }
}
