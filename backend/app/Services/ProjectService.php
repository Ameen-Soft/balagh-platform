<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectContribution;
use App\Models\ProjectPhase;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProjectService
{
    public function __construct(
        protected NotificationService $notificationService,
        protected FileUploadService $fileUploadService
    ) {}

    /**
     * List projects with optional filtering, search, and pagination.
     *
     * @param  array<string, mixed>  $filters
     */
    public function listProjects(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Project::with(['department.ministry', 'phases']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (! empty($filters['ministry_id'])) {
            $query->whereHas('department', function ($q) use ($filters) {
                $q->where('ministry_id', $filters['ministry_id']);
            });
        }

        if (! empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                    ->orWhere('description', 'like', $search);
            });
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);

        $perPage = (int) ($filters['per_page'] ?? $perPage);

        return $query->paginate($perPage);
    }

    /**
     * Get detailed project information with all relations.
     */
    public function getProjectDetails(Project $project): Project
    {
        return $project->load([
            'department.ministry',
            'phases',
            'createdBy',
            'contributions.contributor',
        ]);
    }

    /**
     * Record a simulated financial contribution with pessimistic locking and BCMath accuracy.
     *
     * @throws ValidationException
     */
    public function recordContribution(Project $project, float $amount, User $contributor): ProjectContribution
    {
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => ['مبلغ المساهمة يجب أن يكون أكبر من الصفر.'],
            ]);
        }

        $contribution = DB::transaction(function () use ($project, $amount, $contributor) {
            // Pessimistic locking to avoid race conditions on financial totals
            $lockedProject = Project::where('id', $project->id)->lockForUpdate()->first();

            if (! $lockedProject || ! in_array($lockedProject->status, ['active', 'published', 'in_progress'])) {
                throw ValidationException::withMessages([
                    'project' => ['المشروع غير متاح لقبول مساهمات جديدة حالياً.'],
                ]);
            }

            // High precision calculation using BCMath
            $formattedAmount = number_format($amount, 2, '.', '');
            $newCurrentAmount = bcadd((string) $lockedProject->current_amount, $formattedAmount, 2);

            $updateData = ['current_amount' => $newCurrentAmount];

            // If target amount reached, mark project as completed
            if (bccomp($newCurrentAmount, (string) $lockedProject->target_amount, 2) >= 0) {
                $updateData['status'] = 'completed';
            }

            $lockedProject->update($updateData);

            return ProjectContribution::create([
                'project_id' => $lockedProject->id,
                'contributor_id' => $contributor->id,
                'amount' => $amount,
            ]);
        });

        // Post-Commit Notification (Side-effect)
        $this->notificationService->send(
            userId: $contributor->id,
            title: 'شكراً لمساهمتك التنموية',
            body: "تم تسجيل مساهمتك بمبلغ {$amount} ريال في مشروع '{$project->title}' بنجاح.",
            type: 'project_contribution_success',
            data: [
                'project_id' => $project->id,
                'contribution_id' => $contribution->id,
                'amount' => $amount,
            ]
        );

        return $contribution->load(['project', 'contributor']);
    }

    /**
     * Create a developmental project with optional image upload.
     *
     * @param  array<string, mixed>  $data
     */
    public function createProject(array $data, ?UploadedFile $image = null, ?User $creator = null): Project
    {
        $creatorId = $creator?->id ?? auth()->id();

        if ($image) {
            $uploaded = $this->fileUploadService->upload($image, 'projects');
            $data['image_path'] = $uploaded['file_path'];
        }

        $data['created_by'] = $creatorId;
        $data['current_amount'] = $data['current_amount'] ?? 0;
        $data['status'] = $data['status'] ?? 'active';

        return Project::create($data)->load(['department.ministry', 'createdBy']);
    }

    /**
     * Update an existing developmental project.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateProject(Project $project, array $data, ?UploadedFile $image = null): Project
    {
        if ($image) {
            if ($project->image_path) {
                $this->fileUploadService->delete($project->image_path);
            }
            $uploaded = $this->fileUploadService->upload($image, 'projects');
            $data['image_path'] = $uploaded['file_path'];
        }

        $project->update($data);

        return $project->load(['department.ministry', 'createdBy', 'phases']);
    }

    /**
     * Delete a developmental project and its image.
     */
    public function deleteProject(Project $project): bool
    {
        if ($project->image_path) {
            $this->fileUploadService->delete($project->image_path);
        }

        return (bool) $project->delete();
    }

    /**
     * Create an executive phase for a project.
     *
     * @param  array<string, mixed>  $data
     */
    public function createPhase(Project $project, array $data): ProjectPhase
    {
        $data['project_id'] = $project->id;
        $data['description'] = $data['description'] ?? '';
        $data['completion_percentage'] = (int) ($data['completion_percentage'] ?? 0);
        $data['status'] = $data['status'] ?? 'pending';

        return ProjectPhase::create($data);
    }

    /**
     * Update an executive phase.
     *
     * @param  array<string, mixed>  $data
     */
    public function updatePhase(ProjectPhase $phase, array $data): ProjectPhase
    {
        if (isset($data['completion_percentage'])) {
            $data['completion_percentage'] = (int) $data['completion_percentage'];
        }
        if (array_key_exists('description', $data) && $data['description'] === null) {
            $data['description'] = '';
        }

        $phase->update($data);

        return $phase;
    }

    /**
     * Delete an executive phase.
     */
    public function deletePhase(ProjectPhase $phase): bool
    {
        return (bool) $phase->delete();
    }
}
