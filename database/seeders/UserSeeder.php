<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Laravel\Jetstream\Features;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Development & Testing environment password only
        $defaultPassword = Hash::make('password');

        $roadsDept = Department::where('name', 'إدارة الطرق والجسور')->first();

        // Roles
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        $ministryAdminRole = Role::where('name', 'Ministry Admin')->first();
        $fieldWorkerRole = Role::where('name', 'Field Worker')->first();
        $citizenRole = Role::where('name', 'Citizen')->first();

        $users = [
            [
                'email' => 'admin@example.test',
                'name' => 'المدير العام للنظام',
                'phone' => '770000001',
                'national_id' => '100000000001',
                'department_id' => null,
                'is_active' => true,
                'role' => $superAdminRole,
            ],
            [
                'email' => 'ministry@example.test',
                'name' => 'مشرف وزارة الأشغال',
                'phone' => '770000002',
                'national_id' => '100000000002',
                'department_id' => $roadsDept?->id,
                'is_active' => true,
                'role' => $ministryAdminRole,
            ],
            [
                'email' => 'worker@example.test',
                'name' => 'مهندس الصيانة الميدانية',
                'phone' => '770000003',
                'national_id' => '100000000003',
                'department_id' => $roadsDept?->id,
                'is_active' => true,
                'role' => $fieldWorkerRole,
            ],
            [
                'email' => 'citizen@example.test',
                'name' => 'أحمد المواطن الصالح',
                'phone' => '770000004',
                'national_id' => '100000000004',
                'department_id' => null,
                'is_active' => true,
                'role' => $citizenRole,
            ],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, [
                    'password' => $defaultPassword,
                    'email_verified_at' => now(),
                ])
            );

            // Sync role
            if ($role) {
                $user->roles()->syncWithoutDetaching([$role->id]);
            }

            // Create personal team for Jetstream if enabled and not already created
            if (Features::hasTeamFeatures() && $user->ownedTeams()->count() === 0) {
                $user->ownedTeams()->save(Team::forceCreate([
                    'user_id' => $user->id,
                    'name' => explode(' ', $user->name, 2)[0]."'s Team",
                    'personal_team' => true,
                ]));
            }
        }
    }
}
