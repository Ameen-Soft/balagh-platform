<?php

namespace Tests\Feature\Ministry;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\Department;
use App\Models\Ministry;
use App\Models\Role;
use App\Models\User;
use App\Services\ComplaintService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MinistryStructureAndCategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $ministryAdmin;

    protected User $otherMinistryAdmin;

    protected User $citizen;

    protected Ministry $ministry;

    protected Ministry $otherMinistry;

    protected Department $department;

    protected Department $otherDepartment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        // Super Admin
        $superAdminRole = Role::where('name', 'Super Admin')->firstOrFail();
        $this->superAdmin = User::factory()->create(['is_active' => true]);
        $this->superAdmin->roles()->attach($superAdminRole);

        // Ministry 1
        $this->ministry = Ministry::create([
            'name' => 'وزارة الأشغال العامة والطرق',
            'code' => 'MOPW',
            'logo' => 'logos/mopw.png',
            'contact_email' => 'mopw@example.test',
            'is_active' => true,
        ]);
        $this->department = Department::create([
            'ministry_id' => $this->ministry->id,
            'name' => 'إدارة الطرق والجسور',
            'description' => 'صيانة شبكات الطرق والجسور',
            'is_active' => true,
        ]);

        // Ministry 2
        $this->otherMinistry = Ministry::create([
            'name' => 'وزارة المياه والبيئة',
            'code' => 'MOWE',
            'logo' => 'logos/mowe.png',
            'contact_email' => 'mowe@example.test',
            'is_active' => true,
        ]);
        $this->otherDepartment = Department::create([
            'ministry_id' => $this->otherMinistry->id,
            'name' => 'مؤسسة المياه',
            'description' => 'إمدادات المياه والصرف الصحي',
            'is_active' => true,
        ]);

        // Roles
        $ministryAdminRole = Role::where('name', 'Ministry Admin')->firstOrFail();
        $citizenRole = Role::where('name', 'Citizen')->firstOrFail();

        // Ministry Admin 1
        $this->ministryAdmin = User::factory()->create([
            'department_id' => $this->department->id,
            'is_active' => true,
        ]);
        $this->ministryAdmin->roles()->attach($ministryAdminRole);

        // Ministry Admin 2
        $this->otherMinistryAdmin = User::factory()->create([
            'department_id' => $this->otherDepartment->id,
            'is_active' => true,
        ]);
        $this->otherMinistryAdmin->roles()->attach($ministryAdminRole);

        // Citizen
        $this->citizen = User::factory()->create(['is_active' => true]);
        $this->citizen->roles()->attach($citizenRole);
    }

    /**
     * 1. Super Admin can view ministries and see eligible users for admin assignment.
     */
    public function test_super_admin_can_view_ministries_and_see_eligible_users(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('admin.ministries.index'));

        $response->assertStatus(200);
        $response->assertSee('إدارة الهيكل المؤسسي والوزارات');
        $response->assertSee($this->ministry->name);
        $response->assertSee('userSearchPicker', false);

        // Verify eligible users are searchable via optimized search endpoint
        $searchResponse = $this->actingAs($this->superAdmin)
            ->getJson(route('internal.search.users', [
                'query' => $this->citizen->name,
                'scope' => 'ministry_candidates',
            ]));

        $searchResponse->assertOk();
        $this->assertContains($this->citizen->id, collect($searchResponse->json('data'))->pluck('id'));
    }

    /**
     * 2. Super Admin can create ministry without code, and a code is auto-generated.
     */
    public function test_super_admin_can_create_ministry_without_code_and_auto_generates_code(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.ministries.store'), [
                'name' => 'وزارة الاتصالات وتقنية المعلومات',
                'contact_email' => 'telecom@example.test',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ministries', [
            'name' => 'وزارة الاتصالات وتقنية المعلومات',
            'contact_email' => 'telecom@example.test',
        ]);

        $created = Ministry::where('name', 'وزارة الاتصالات وتقنية المعلومات')->first();
        $this->assertNotNull($created);
        $this->assertNotEmpty($created->code);
    }

    /**
     * 3. Super Admin can create ministry and assign a registered citizen as ministry admin.
     */
    public function test_super_admin_can_create_ministry_and_assign_responsible_admin(): void
    {
        $newAdminUser = User::factory()->create(['is_active' => true]);
        $citizenRole = Role::where('name', 'Citizen')->firstOrFail();
        $newAdminUser->roles()->attach($citizenRole);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.ministries.store'), [
                'name' => 'وزارة الصحة العامة والسكان',
                'contact_email' => 'health@example.test',
                'admin_user_id' => $newAdminUser->id,
            ]);

        $response->assertRedirect();

        $healthMinistry = Ministry::where('name', 'وزارة الصحة العامة والسكان')->firstOrFail();
        $newAdminUser->refresh();

        // User should now be assigned to a department in the new ministry
        $this->assertNotNull($newAdminUser->department_id);
        $this->assertEquals($healthMinistry->id, $newAdminUser->department->ministry_id);

        // User should have Ministry Admin role
        $this->assertTrue($newAdminUser->hasRole('Ministry Admin'));
    }

    /**
     * 4. Super Admin cannot be assigned as a Ministry Admin.
     */
    public function test_super_admin_cannot_assign_another_super_admin_as_ministry_admin(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.ministries.store'), [
                'name' => 'وزارة التخطيط والتعاون الدولي',
                'contact_email' => 'planning@example.test',
                'admin_user_id' => $this->superAdmin->id,
            ]);

        $response->assertSessionHasErrors('admin_user_id');
        $this->assertDatabaseMissing('ministries', ['name' => 'وزارة التخطيط والتعاون الدولي']);
    }

    /**
     * 5. Inactive user cannot be assigned as Ministry Admin.
     */
    public function test_super_admin_cannot_assign_inactive_user_as_ministry_admin(): void
    {
        $inactiveUser = User::factory()->create(['is_active' => false]);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.ministries.store'), [
                'name' => 'وزارة النقل',
                'contact_email' => 'transport@example.test',
                'admin_user_id' => $inactiveUser->id,
            ]);

        $response->assertSessionHasErrors('admin_user_id');
        $this->assertDatabaseMissing('ministries', ['name' => 'وزارة النقل']);
    }

    /**
     * 6. Super Admin can assign/reassign admin to an existing ministry.
     */
    public function test_super_admin_can_assign_admin_to_existing_ministry(): void
    {
        $candidateUser = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.ministries.assign_admin', $this->ministry), [
                'admin_user_id' => $candidateUser->id,
            ]);

        $response->assertRedirect();
        $candidateUser->refresh();

        $this->assertTrue($candidateUser->hasRole('Ministry Admin'));
        $this->assertEquals($this->ministry->id, $candidateUser->department->ministry_id);
    }

    /**
     * 7. Ministry Admin can view departments index scoped to their ministry.
     */
    public function test_ministry_admin_can_view_departments_index_scoped_to_their_ministry(): void
    {
        $response = $this->actingAs($this->ministryAdmin)
            ->get(route('ministry.departments.index'));

        $response->assertStatus(200);
        $response->assertSee('إدارة وهيكلية الإدارات الخدمية');
        $response->assertSee($this->department->name);
        // Should NOT see departments of other ministries
        $response->assertDontSee($this->otherDepartment->name);
    }

    /**
     * 8. Ministry Admin can create service department automatically linked to their ministry.
     */
    public function test_ministry_admin_can_create_service_department_automatically_linked_to_their_ministry(): void
    {
        $response = $this->actingAs($this->ministryAdmin)
            ->post(route('ministry.departments.store'), [
                'name' => 'إدارة الإنارة والطاقة الشمسية',
                'description' => 'صيانة واستبدال أعمدة الإنارة في الشوارع العامة',
            ]);

        $response->assertRedirect(route('ministry.departments.index'));
        $this->assertDatabaseHas('departments', [
            'name' => 'إدارة الإنارة والطاقة الشمسية',
            'ministry_id' => $this->ministry->id,
            'is_active' => true,
        ]);
    }

    /**
     * 9. Citizen cannot access ministry departments or categories management (403).
     */
    public function test_citizen_cannot_access_ministry_departments_or_categories(): void
    {
        $this->actingAs($this->citizen)
            ->get(route('ministry.departments.index'))
            ->assertStatus(403);

        $this->actingAs($this->citizen)
            ->get(route('ministry.categories.index'))
            ->assertStatus(403);
    }

    /**
     * 10. Ministry Admin can view categories index scoped to their ministry.
     */
    public function test_ministry_admin_can_view_categories_index_scoped_to_their_ministry(): void
    {
        $cat1 = Category::create([
            'department_id' => $this->department->id,
            'name' => 'هبوط إسفلتي',
            'description' => 'تصدعات وهبوط في الطبقة الإسفلتية',
            'level' => 1,
        ]);

        $catOther = Category::create([
            'department_id' => $this->otherDepartment->id,
            'name' => 'كسر في ماسورة رئيسية',
            'description' => 'انفجار أنبوب مياه',
            'level' => 1,
        ]);

        $response = $this->actingAs($this->ministryAdmin)
            ->get(route('ministry.categories.index'));

        $response->assertStatus(200);
        $response->assertSee('هبوط إسفلتي');
        $response->assertDontSee('كسر في ماسورة رئيسية');
    }

    /**
     * 11. Ministry Admin can create root category for their department.
     */
    public function test_ministry_admin_can_create_root_category_for_their_department(): void
    {
        $response = $this->actingAs($this->ministryAdmin)
            ->post(route('ministry.categories.store'), [
                'name' => 'انهيارات الجسور الجبلية',
                'department_id' => $this->department->id,
                'description' => 'بلاغات تصدع الجسور وجدران السند',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'name' => 'انهيارات الجسور الجبلية',
            'department_id' => $this->department->id,
            'level' => 1,
            'parent_id' => null,
        ]);
    }

    /**
     * 12. Ministry Admin can create subcategory linked to valid parent in the same ministry.
     */
    public function test_ministry_admin_can_create_subcategory_linked_to_valid_parent_in_same_ministry(): void
    {
        $rootCat = Category::create([
            'department_id' => $this->department->id,
            'name' => 'صيانة الطرق',
            'description' => 'أعمال عامة للطرق',
            'level' => 1,
        ]);

        $response = $this->actingAs($this->ministryAdmin)
            ->post(route('ministry.categories.store'), [
                'name' => 'حفر صغيرة في الأحياء',
                'department_id' => $this->department->id,
                'parent_id' => $rootCat->id,
                'description' => 'الحفر الثانوية',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'name' => 'حفر صغيرة في الأحياء',
            'department_id' => $this->department->id,
            'parent_id' => $rootCat->id,
            'level' => 2,
        ]);
    }

    /**
     * 13. Ministry Admin cannot create category linked to department of another ministry.
     */
    public function test_ministry_admin_cannot_create_category_linked_to_department_of_another_ministry(): void
    {
        $response = $this->actingAs($this->ministryAdmin)
            ->post(route('ministry.categories.store'), [
                'name' => 'تسرب مياه',
                'department_id' => $this->otherDepartment->id, // belongs to MOWE
                'description' => 'محاولة غير مصرح بها',
            ]);

        $response->assertSessionHasErrors('department_id');
        $this->assertDatabaseMissing('categories', ['name' => 'تسرب مياه']);
    }

    /**
     * 14. Ministry Admin cannot link subcategory to parent from another ministry.
     */
    public function test_ministry_admin_cannot_link_subcategory_to_parent_from_another_ministry(): void
    {
        $foreignRootCat = Category::create([
            'department_id' => $this->otherDepartment->id,
            'name' => 'شبكات الصرف الصحي',
            'description' => 'مياه وبيئة',
            'level' => 1,
        ]);

        $response = $this->actingAs($this->ministryAdmin)
            ->post(route('ministry.categories.store'), [
                'name' => 'انسداد منهول',
                'department_id' => $this->department->id,
                'parent_id' => $foreignRootCat->id,
                'description' => 'ربط غير صالح',
            ]);

        $response->assertSessionHasErrors('parent_id');
        $this->assertDatabaseMissing('categories', ['name' => 'انسداد منهول']);
    }

    /**
     * 15. Ministry Admin cannot link subcategory to a category that is not level 1.
     */
    public function test_ministry_admin_cannot_link_subcategory_to_non_root_category(): void
    {
        $root = Category::create([
            'department_id' => $this->department->id,
            'name' => 'التصنيف الرئيسي',
            'description' => 'مستوى 1',
            'level' => 1,
        ]);

        $sub = Category::create([
            'department_id' => $this->department->id,
            'parent_id' => $root->id,
            'name' => 'التصنيف الفرعي',
            'description' => 'مستوى 2',
            'level' => 2,
        ]);

        $response = $this->actingAs($this->ministryAdmin)
            ->post(route('ministry.categories.store'), [
                'name' => 'تصنيف مستوى 3 مرفوض',
                'department_id' => $this->department->id,
                'parent_id' => $sub->id,
                'description' => 'تجاوز العمق المسموح',
            ]);

        $response->assertSessionHasErrors('parent_id');
        $this->assertDatabaseMissing('categories', ['name' => 'تصنيف مستوى 3 مرفوض']);
    }

    /**
     * 16. New category created by ministry routes incoming complaints automatically to the correct department.
     */
    public function test_new_ministry_category_is_routed_automatically_to_its_department_on_complaint_submission(): void
    {
        // 1. Create a new department for the ministry
        $newDept = Department::create([
            'ministry_id' => $this->ministry->id,
            'name' => 'إدارة الطوارئ والإنقاذ السريع',
            'description' => 'معالجة الطوارئ العاجلة',
            'is_active' => true,
        ]);

        // 2. Create category under this department
        $emergencyCat = Category::create([
            'department_id' => $newDept->id,
            'name' => 'انهيار صخري مفاجئ',
            'description' => 'طوارئ الطرق الجبلية',
            'level' => 1,
        ]);

        // 3. Citizen submits a complaint using this new category
        $complaintService = app(ComplaintService::class);
        $complaint = $complaintService->createComplaint([
            'category_id' => $emergencyCat->id,
            'title' => 'تساقط صخور في نقيل يسلح',
            'description' => 'انهيار صخري يعيق حركة السير بالكامل',
            'priority' => 'urgent',
            'latitude' => 15.3500,
            'longitude' => 44.2000,
            'is_public' => false,
        ], $this->citizen);

        // 4. Assert routing
        $this->assertEquals($newDept->id, $complaint->current_department_id);
        $this->assertEquals($this->ministry->id, $complaint->currentDepartment->ministry_id);
        $this->assertContains($complaint->status, ['new', 'submitted']);
    }
}
