<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UserSearchService
{
    /**
     * Search users with optimized indexing, field selection, and strict authorization scoping.
     *
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function search(string $query, string $scope, User $authUser, array $filters = []): Collection
    {
        $trimmed = trim($query);
        if ($trimmed === '') {
            return collect();
        }

        $baseQuery = User::query()
            ->select(['id', 'name', 'email', 'phone', 'national_id', 'department_id', 'is_active'])
            ->with([
                'roles:id,name',
                'department:id,name,ministry_id',
            ]);

        // 1. Apply Scope & Authorization Guard
        switch ($scope) {
            case 'ministry_candidates':
                if (! $authUser->hasRole('Super Admin')) {
                    throw new AccessDeniedHttpException('غير مصرح لك بالبحث عن مسؤولي الوزارات.');
                }

                $baseQuery->where('is_active', true)
                    ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'Super Admin'));
                break;

            case 'citizens_for_promotion':
                if (! $authUser->hasRole('Ministry Admin') && ! $authUser->hasRole('Super Admin')) {
                    throw new AccessDeniedHttpException('غير مصرح لك بالبحث عن مواطنين للترقية.');
                }

                $baseQuery->where('is_active', true)
                    ->whereHas('roles', fn ($q) => $q->where('name', 'Citizen'))
                    ->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', ['Super Admin', 'Ministry Admin', 'Field Worker']));
                break;

            case 'department_workers':
                if (! $authUser->hasRole('Ministry Admin') && ! $authUser->hasRole('Super Admin')) {
                    throw new AccessDeniedHttpException('غير مصرح لك باستعراض الكادر الميداني.');
                }

                $departmentId = $authUser->department_id;
                if (! $departmentId && ! $authUser->hasRole('Super Admin')) {
                    throw new AccessDeniedHttpException('حساب المشرف غير مرتبط بقسم حكومي.');
                }

                $baseQuery->where('is_active', true)
                    ->whereHas('roles', fn ($q) => $q->where('name', 'Field Worker'));

                if ($departmentId && ! $authUser->hasRole('Super Admin')) {
                    $baseQuery->where('department_id', $departmentId);
                }
                break;

            case 'admin_users':
                if (! $authUser->hasRole('Super Admin')) {
                    throw new AccessDeniedHttpException('غير مصرح لك بإدارة والبحث في سجلات المستخدمين.');
                }

                if (! empty($filters['role'])) {
                    $baseQuery->whereHas('roles', fn ($q) => $q->where('name', $filters['role']));
                }

                if (! empty($filters['department_id'])) {
                    $baseQuery->where('department_id', $filters['department_id']);
                }
                break;

            default:
                throw new \InvalidArgumentException("نطاق البحث المحدد [{$scope}] غير معروف.");
        }

        // 2. High-Performance Index-Friendly Query Matching
        $this->applyOptimizedMatching($baseQuery, $trimmed);

        // 3. Strict Result Capping
        $limit = min((int) ($filters['limit'] ?? 15), 30);
        $users = $baseQuery->latest('id')->limit($limit)->get();

        // 4. Format without leaking sensitive credentials
        return $users->map(fn (User $user) => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'national_id' => $user->national_id,
            'roles' => $user->roles->pluck('name')->all(),
            'department_id' => $user->department_id,
            'department_name' => $user->department?->name,
            'is_active' => (bool) $user->is_active,
        ]);
    }

    /**
     * Apply index-friendly prefix queries where possible to avoid full table scans.
     */
    protected function applyOptimizedMatching($query, string $term): void
    {
        // Numeric search (phone or national_id)
        if (preg_match('/^[0-9+]+$/', $term)) {
            $query->where(function ($q) use ($term) {
                $q->where('phone', 'like', "{$term}%")
                    ->orWhere('national_id', 'like', "{$term}%");
            });

            return;
        }

        // Email search (contains @)
        if (str_contains($term, '@')) {
            $query->where('email', 'like', "{$term}%");

            return;
        }

        // Text search (name, email, phone)
        // Primary search using prefix index (FAST on millions of rows)
        $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "{$term}%")
                ->orWhere('email', 'like', "{$term}%")
                ->orWhere('phone', 'like', "{$term}%")
                ->orWhere('national_id', 'like', "{$term}%");
        });
    }
}
