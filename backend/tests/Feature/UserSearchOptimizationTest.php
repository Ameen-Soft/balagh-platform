<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Ministry;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserSearchOptimizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $ministryAdmin;
    protected User $otherMinistryAdmin;
    protected User $fieldWorker;
    protected User $otherFieldWorker;
    protected User $citizen;
    protected Department $department;
    protected Department $otherDepartment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $superAdminRole = Role::where('name', 'Super Admin')->firstOrFail();
        $ministryAdminRole = Role::where('name', 'Ministry Admin')->firstOrFail();
        $fieldWorkerRole = Role::where('name', 'Field Worker')->firstOrFail();
        $citizenRole = Role::where('name', 'Citizen')->firstOrFail();

        $ministry = Ministry::create([
            'name' => 'وزارة الأشغال العامة والطرق',
            'code' => 'MOPW',
            'logo' => 'logos/mopw.png',
            'contact_email' => 'mopw@example.test',
            'is_active' => true,
        ]);

        $this->department = Department::create([
            'ministry_id' => $ministry->id,
            'name' => 'إدارة الطرق والجسور',
            'description' => 'صيانة شبكات الطرق',
            'is_active' => true,
        ]);

        $otherMinistry = Ministry::create([
            'name' => 'وزارة الكهرباء والطاقة',
            'code' => 'MOEE',
            'logo' => 'logos/moee.png',
            'contact_email' => 'moee@example.test',
            'is_active' => true,
        ]);

        $this->otherDepartment = Department::create([
            'ministry_id' => $otherMinistry->id,
            'name' => 'إدارة التوزيع',
            'description' => 'توزيع شبكات الكهرباء',
            'is_active' => true,
        ]);

        $this->superAdmin = User::factory()->create([
            'name' => 'المدير العام للنظام',
            'email' => 'admin@example.test',
            'phone' => '770000001',
            'national_id' => '1000000001',
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);
        $this->superAdmin->roles()->sync([$superAdminRole->id]);

        $this->ministryAdmin = User::factory()->create([
            'name' => 'مشرف وزارة الأشغال',
            'email' => 'works.admin@example.test',
            'phone' => '770000002',
            'national_id' => '1000000002',
            'department_id' => $this->department->id,
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);
        $this->ministryAdmin->roles()->sync([$ministryAdminRole->id]);

        $this->otherMinistryAdmin = User::factory()->create([
            'name' => 'مشرف وزارة الكهرباء',
            'email' => 'power.admin@example.test',
            'phone' => '770000003',
            'national_id' => '1000000003',
            'department_id' => $this->otherDepartment->id,
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);
        $this->otherMinistryAdmin->roles()->sync([$ministryAdminRole->id]);

        $this->fieldWorker = User::factory()->create([
            'name' => 'محمد مهندس أشغال',
            'email' => 'worker.works@example.test',
            'phone' => '771111111',
            'national_id' => '1000000010',
            'department_id' => $this->department->id,
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);
        $this->fieldWorker->roles()->sync([$fieldWorkerRole->id]);

        $this->otherFieldWorker = User::factory()->create([
            'name' => 'علي فني كهرباء',
            'email' => 'worker.power@example.test',
            'phone' => '772222222',
            'national_id' => '1000000020',
            'department_id' => $this->otherDepartment->id,
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);
        $this->otherFieldWorker->roles()->sync([$fieldWorkerRole->id]);

        $this->citizen = User::factory()->create([
            'name' => 'خالد عبد الله مواطن',
            'email' => 'khaled.citizen@example.test',
            'phone' => '773333333',
            'national_id' => '1000000030',
            'department_id' => null,
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);
        $this->citizen->roles()->sync([$citizenRole->id]);
    }

    public function test_unauthenticated_user_cannot_access_search_endpoint(): void
    {
        $response = $this->getJson(route('internal.search.users', [
            'query' => 'خالد',
            'scope' => 'citizens_for_promotion',
        ]));

        $response->assertUnauthorized();
    }

    public function test_super_admin_can_search_ministry_candidates(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $response = $this->getJson(route('internal.search.users', [
            'query' => 'مشرف',
            'scope' => 'ministry_candidates',
        ]));

        $response->assertOk()
            ->assertJsonPath('success', true);

        $data = $response->json('data');
        $this->assertNotEmpty($data);
        
        // Super admin must never appear in ministry candidates
        $ids = collect($data)->pluck('id');
        $this->assertNotContains($this->superAdmin->id, $ids);
        $this->assertContains($this->ministryAdmin->id, $ids);
    }

    public function test_ministry_admin_forbidden_from_searching_ministry_candidates(): void
    {
        Sanctum::actingAs($this->ministryAdmin);

        $response = $this->getJson(route('internal.search.users', [
            'query' => 'أحمد',
            'scope' => 'ministry_candidates',
        ]));

        $response->assertForbidden();
    }

    public function test_ministry_admin_can_search_citizens_for_promotion(): void
    {
        Sanctum::actingAs($this->ministryAdmin);

        // Search by name prefix
        $response = $this->getJson(route('internal.search.users', [
            'query' => 'خالد',
            'scope' => 'citizens_for_promotion',
        ]));

        $response->assertOk()
            ->assertJsonPath('success', true);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($this->citizen->id, $data[0]['id']);

        // Search by phone prefix
        $responsePhone = $this->getJson(route('internal.search.users', [
            'query' => '7733',
            'scope' => 'citizens_for_promotion',
        ]));

        $responsePhone->assertOk();
        $this->assertEquals($this->citizen->id, $responsePhone->json('data.0.id'));

        // Search by national_id prefix
        $responseNationalId = $this->getJson(route('internal.search.users', [
            'query' => '1000000030',
            'scope' => 'citizens_for_promotion',
        ]));

        $responseNationalId->assertOk();
        $this->assertEquals($this->citizen->id, $responseNationalId->json('data.0.id'));
    }

    public function test_regular_citizen_cannot_search_citizens_for_promotion(): void
    {
        Sanctum::actingAs($this->citizen);

        $response = $this->getJson(route('internal.search.users', [
            'query' => 'خالد',
            'scope' => 'citizens_for_promotion',
        ]));

        $response->assertForbidden();
    }

    public function test_ministry_admin_department_workers_search_is_strictly_scoped(): void
    {
        Sanctum::actingAs($this->ministryAdmin);

        $response = $this->getJson(route('internal.search.users', [
            'query' => '77',
            'scope' => 'department_workers',
        ]));

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id');

        // Must find its own worker
        $this->assertContains($this->fieldWorker->id, $ids);

        // Must NEVER leak workers from other departments
        $this->assertNotContains($this->otherFieldWorker->id, $ids);
    }

    public function test_search_never_leaks_sensitive_credentials(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $response = $this->getJson(route('internal.search.users', [
            'query' => 'admin',
            'scope' => 'admin_users',
        ]));

        $response->assertOk();
        $userPayload = $response->json('data.0');

        $this->assertArrayHasKey('id', $userPayload);
        $this->assertArrayHasKey('name', $userPayload);
        $this->assertArrayHasKey('email', $userPayload);
        $this->assertArrayNotHasKey('password', $userPayload);
        $this->assertArrayNotHasKey('remember_token', $userPayload);
        $this->assertArrayNotHasKey('two_factor_secret', $userPayload);
    }

    public function test_search_respects_result_capping(): void
    {
        Sanctum::actingAs($this->superAdmin);

        // Generate 35 citizen users
        $citizenRole = Role::where('name', 'Citizen')->firstOrFail();
        for ($i = 0; $i < 35; $i++) {
            $u = User::factory()->create([
                'name' => "مستخدم اختباري {$i}",
                'email' => "bulk_test_{$i}@example.test",
                'phone' => "7799999" . sprintf('%03d', $i),
                'national_id' => "2000000" . sprintf('%03d', $i),
                'is_active' => true,
            ]);
            $u->roles()->sync([$citizenRole->id]);
        }

        $response = $this->getJson(route('internal.search.users', [
            'query' => 'مستخدم اختباري',
            'scope' => 'citizens_for_promotion',
            'limit' => 100, // Client requests 100
        ]));

        $response->assertOk();
        // Server caps at maximum 30
        $this->assertLessThanOrEqual(30, count($response->json('data')));
    }

    public function test_empty_query_fails_validation(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $response = $this->getJson(route('internal.search.users', [
            'query' => '',
            'scope' => 'admin_users',
        ]));

        $response->assertUnprocessable();
    }

    public function test_admin_ministries_page_renders_with_user_search_picker(): void
    {
        $this->actingAs($this->superAdmin);

        $response = $this->get(route('admin.ministries.index'));

        $response->assertOk();
        $response->assertSee('x-data="userSearchPicker', false);
        $response->assertSee("scope: 'ministry_candidates'", false);
    }

    public function test_ministry_workers_page_renders_with_instant_search_and_promotion(): void
    {
        $this->actingAs($this->ministryAdmin);

        $response = $this->get(route('ministry.workers.index'));

        $response->assertOk();
        $response->assertSee("scope: 'citizens_for_promotion'", false);
        $response->assertSee('ترقية الحساب المختار');
    }
}
