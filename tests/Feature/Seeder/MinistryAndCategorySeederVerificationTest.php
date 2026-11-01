<?php

namespace Tests\Feature\Seeder;

use App\Models\Category;
use App\Models\Department;
use App\Models\Ministry;
use App\Models\Role;
use App\Models\User;
use App\Services\RoutingService;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\MinistrySeeder;
use Database\Seeders\RolePermissionSeeder;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MinistryAndCategorySeederVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1. Test that all 20 approved ministries are seeded correctly.
     */
    public function test_seeds_all_twenty_approved_ministries(): void
    {
        $this->seed(MinistrySeeder::class);

        $this->assertEquals(20, Ministry::count());

        $expectedCodes = [
            'MOPW', 'MOEE', 'MOWE', 'MOITT', 'MOT',
            'MOLA', 'MOPIC', 'MOPHP', 'MOIC', 'MOPM',
            'MAI', 'MOFW', 'MOE', 'MOTEVT', 'MOSAL',
            'MOAE', 'MOYS', 'MOTOUR', 'MOC', 'MOI',
        ];

        foreach ($expectedCodes as $code) {
            $this->assertDatabaseHas('ministries', [
                'code' => $code,
                'is_active' => true,
            ]);
        }
    }

    /**
     * 2. Test that all departments are seeded and accurately linked to their ministries with no orphans.
     */
    public function test_seeds_departments_and_links_to_proper_ministries_without_orphans(): void
    {
        $this->seed(MinistrySeeder::class);
        $this->seed(DepartmentSeeder::class);

        $departmentCount = Department::count();
        $this->assertEquals(68, $departmentCount);

        // Assert no orphan department
        $orphanCount = Department::whereNull('ministry_id')->count();
        $this->assertEquals(0, $orphanCount);

        // Every department has valid ministry
        foreach (Department::with('ministry')->get() as $dept) {
            $this->assertNotNull($dept->ministry, "Department {$dept->name} must have a valid ministry");
            $this->assertNotEmpty($dept->description, "Department {$dept->name} must have a description");
            $this->assertTrue($dept->is_active);
        }
    }

    /**
     * 3. Test that root categories and subcategories obey the hierarchy and tri-partite integrity.
     */
    public function test_categories_hierarchy_and_tripartite_integrity(): void
    {
        $this->seed(MinistrySeeder::class);
        $this->seed(DepartmentSeeder::class);
        $this->seed(CategorySeeder::class);

        // 20 Root categories (one per ministry)
        $rootCategories = Category::whereNull('parent_id')->get();
        $this->assertCount(20, $rootCategories);

        foreach ($rootCategories as $root) {
            $this->assertEquals(1, $root->level, "Root category {$root->name} must be level 1");
            $this->assertNull($root->department_id, "Root category {$root->name} must have null department_id");
            $this->assertNotNull($root->ministry_id, "Root category {$root->name} must have ministry_id");
            $this->assertTrue($root->is_active);
        }

        // Subcategories (level 2)
        $subCategories = Category::whereNotNull('parent_id')->get();
        $this->assertCount(99, $subCategories);

        foreach ($subCategories as $sub) {
            $this->assertEquals(2, $sub->level, "Subcategory {$sub->name} must be level 2");
            $this->assertNotNull($sub->parent_id, "Subcategory {$sub->name} must have parent_id");
            $this->assertNotNull($sub->department_id, "Subcategory {$sub->name} must have department_id");
            $this->assertTrue($sub->is_active);

            // Tri-partite integrity: subcategory.ministry_id == parent.ministry_id == department.ministry_id
            $parent = $sub->parent;
            $department = $sub->department;

            $this->assertNotNull($parent, "Subcategory {$sub->name} parent must exist");
            $this->assertNotNull($department, "Subcategory {$sub->name} department must exist");

            $this->assertEquals(
                $sub->ministry_id,
                $parent->ministry_id,
                "Subcategory {$sub->name} ministry_id must match parent ministry_id"
            );

            $this->assertEquals(
                $sub->ministry_id,
                $department->ministry_id,
                "Subcategory {$sub->name} ministry_id must match department ministry_id"
            );
        }
    }

    /**
     * 4. Test idempotency: running seeders multiple times causes zero duplicates or modifications.
     */
    public function test_seeders_idempotency_on_multiple_executions(): void
    {
        $this->seed(MinistrySeeder::class);
        $this->seed(DepartmentSeeder::class);
        $this->seed(CategorySeeder::class);

        $ministriesCount1 = Ministry::count();
        $departmentsCount1 = Department::count();
        $rootCategoriesCount1 = Category::whereNull('parent_id')->count();
        $subCategoriesCount1 = Category::whereNotNull('parent_id')->count();

        // Run seeders second time
        $this->seed(MinistrySeeder::class);
        $this->seed(DepartmentSeeder::class);
        $this->seed(CategorySeeder::class);

        $ministriesCount2 = Ministry::count();
        $departmentsCount2 = Department::count();
        $rootCategoriesCount2 = Category::whereNull('parent_id')->count();
        $subCategoriesCount2 = Category::whereNotNull('parent_id')->count();

        $this->assertEquals($ministriesCount1, $ministriesCount2);
        $this->assertEquals($departmentsCount1, $departmentsCount2);
        $this->assertEquals($rootCategoriesCount1, $rootCategoriesCount2);
        $this->assertEquals($subCategoriesCount1, $subCategoriesCount2);
    }

    /**
     * 5. Test complete DatabaseSeeder execution and roles/users preservation.
     */
    public function test_full_database_seeder_preserves_roles_and_users(): void
    {
        $this->seed(DatabaseSeeder::class);

        // Verify the 4 mandatory roles
        $mandatoryRoles = ['Super Admin', 'Ministry Admin', 'Field Worker', 'Citizen'];
        foreach ($mandatoryRoles as $roleName) {
            $this->assertDatabaseHas('roles', ['name' => $roleName]);
        }

        // Verify standard test accounts
        $this->assertDatabaseHas('users', ['email' => 'admin@example.test']);
        $this->assertDatabaseHas('users', ['email' => 'ministry@example.test']);
        $this->assertDatabaseHas('users', ['email' => 'worker@example.test']);
        $this->assertDatabaseHas('users', ['email' => 'citizen@example.test']);

        // Running DatabaseSeeder a second time is fully idempotent
        $this->seed(DatabaseSeeder::class);

        $this->assertEquals(4, User::count());
        $this->assertEquals(4, Role::count());
        $this->assertEquals(20, Ministry::count());
        $this->assertEquals(68, Department::count());
        $this->assertEquals(119, Category::count()); // 20 root + 99 sub
    }

    /**
     * 6. Test that RoutingService routes correctly for new seeded categories and rejects root categories.
     */
    public function test_routing_service_with_seeded_data(): void
    {
        $this->seed(MinistrySeeder::class);
        $this->seed(DepartmentSeeder::class);
        $this->seed(CategorySeeder::class);

        $routingService = app(RoutingService::class);

        // Pick a subcategory from a newly added ministry (e.g. MOI: مصلحة الدفاع المدني)
        $fireSafetySub = Category::where('name', 'بلاغات مخاطر الحرائق واشتراطات السلامة والوقاية')->firstOrFail();
        $routeResult = $routingService->route($fireSafetySub->id);

        $moi = Ministry::where('code', 'MOI')->firstOrFail();
        $civilDefenseDept = Department::where('name', 'مصلحة الدفاع المدني')->where('ministry_id', $moi->id)->firstOrFail();

        $this->assertEquals($moi->id, $routeResult['ministry_id']);
        $this->assertEquals($civilDefenseDept->id, $routeResult['department_id']);

        // Verify that root category cannot be used for routing
        $rootCat = Category::where('name', 'السلامة العامة والدفاع المدني والمرور')->firstOrFail();
        $this->expectException(DomainException::class);
        $routingService->route($rootCat->id);
    }
}
