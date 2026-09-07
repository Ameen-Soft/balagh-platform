<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Define technical permission keys
        $permissions = [
            'complaints.create',
            'complaints.view',
            'complaints.review',
            'complaints.transfer',
            'complaints.reject',
            'complaints.close',
            'tasks.view',
            'tasks.assign',
            'tasks.update_status',
            'evidence.upload',
            'ministries.manage',
            'departments.manage',
            'categories.manage',
            'projects.manage',
            'projects.contribute',
            'settings.manage',
            'reports.view',
        ];

        $permissionModels = [];
        foreach ($permissions as $permissionKey) {
            $permissionModels[$permissionKey] = Permission::firstOrCreate(
                ['name' => $permissionKey],
                ['guard_name' => 'web']
            );
        }

        // 2. Define roles and assign permissions
        $roles = [
            'Citizen' => [
                'complaints.create',
                'complaints.view',
                'projects.contribute',
            ],
            'Field Worker' => [
                'tasks.view',
                'tasks.update_status',
                'evidence.upload',
            ],
            'Ministry Admin' => [
                'complaints.view',
                'complaints.review',
                'complaints.transfer',
                'complaints.reject',
                'complaints.close',
                'tasks.view',
                'tasks.assign',
                'reports.view',
            ],
            'Super Admin' => $permissions, // Full permissions
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(
                ['name' => $roleName],
                ['guard_name' => 'web']
            );

            $permissionIds = collect($rolePermissions)
                ->map(fn ($permKey) => $permissionModels[$permKey]->id)
                ->all();

            $role->permissions()->syncWithoutDetaching($permissionIds);
        }
    }
}
