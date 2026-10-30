<?php

namespace Tests\Feature\Category;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\Department;
use App\Models\Ministry;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryHierarchyAndRoutingEngineTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $ministryAdminA;

    protected User $citizen;

    protected Ministry $ministryA;

    protected Ministry $ministryB;

    protected Department $departmentA1;

    protected Department $departmentA2;

    protected Department $departmentB1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $superAdminRole = Role::where('name', 'Super Admin')->firstOrFail();
        $ministryAdminRole = Role::where('name', 'Ministry Admin')->firstOrFail();
        $citizenRole = Role::where('name', 'Citizen')->firstOrFail();

        // 1. Ministries
        $this->ministryA = Ministry::create([
            'name' => 'وزارة الأشغال العامة والطرق',
            'code' => 'MOPW',
            'logo' => 'logos/mopw.png',
            'contact_email' => 'mopw@example.test',
            'is_active' => true,
        ]);

        $this->ministryB = Ministry::create([
            'name' => 'وزارة الكهرباء والطاقة',
            'code' => 'MOEE',
            'logo' => 'logos/moee.png',
            'contact_email' => 'moee@example.test',
            'is_active' => true,
        ]);

        // 2. Departments
        $this->departmentA1 = Department::create([
            'ministry_id' => $this->ministryA->id,
            'name' => 'إدارة الطرق والجسور',
            'description' => 'صيانة شبكات الطرق',
            'is_active' => true,
        ]);

        $this->departmentA2 = Department::create([
            'ministry_id' => $this->ministryA->id,
            'name' => 'إدارة المرافق والمباني الحكومية',
            'description' => 'صيانة المرافق',
            'is_active' => true,
        ]);

        $this->departmentB1 = Department::create([
            'ministry_id' => $this->ministryB->id,
            'name' => 'إدارة صيانة الشبكة الكهربائية',
            'description' => 'صيانة شبكات الكهرباء',
            'is_active' => true,
        ]);

        // 3. Users
        $this->superAdmin = User::factory()->create(['is_active' => true]);
        $this->superAdmin->roles()->attach($superAdminRole);

        $this->ministryAdminA = User::factory()->create([
            'department_id' => $this->departmentA1->id,
            'is_active' => true,
        ]);
        $this->ministryAdminA->roles()->attach($ministryAdminRole);

        $this->citizen = User::factory()->create(['is_active' => true]);
        $this->citizen->roles()->attach($citizenRole);
    }

    /**
     * 1. Main category is linked to ministry directly without department (department_id is null).
     */
    public function test_main_category_is_linked_to_ministry_without_department(): void
    {
        $mainCat = Category::create([
            'ministry_id' => $this->ministryA->id,
            'name' => 'البنية التحتية وشبكة الطرق',
            'description' => 'قطاع الطرق والجسور والمرافق العامة',
            'department_id' => null,
            'parent_id' => null,
            'is_active' => true,
        ]);

        $this->assertEquals(1, $mainCat->level);
        $this->assertNull($mainCat->department_id);
        $this->assertNull($mainCat->parent_id);
        $this->assertEquals($this->ministryA->id, $mainCat->ministry_id);
    }

    /**
     * 2. Subcategory is linked to main category and department of same ministry.
     */
    public function test_subcategory_is_linked_to_parent_and_department_of_same_ministry(): void
    {
        $mainCat = Category::create([
            'ministry_id' => $this->ministryA->id,
            'name' => 'البنية التحتية وشبكة الطرق',
            'parent_id' => null,
            'department_id' => null,
        ]);

        $subCat = Category::create([
            'ministry_id' => $this->ministryA->id,
            'parent_id' => $mainCat->id,
            'department_id' => $this->departmentA1->id,
            'name' => 'انهيار أو تصدع في الجسور',
            'description' => 'تصدعات الجسور',
        ]);

        $this->assertEquals(2, $subCat->level);
        $this->assertEquals($mainCat->id, $subCat->parent_id);
        $this->assertEquals($this->departmentA1->id, $subCat->department_id);
        $this->assertEquals($this->ministryA->id, $subCat->ministry_id);
    }

    /**
     * 3. Cross-ministry linking between parent and department is strictly rejected.
     */
    public function test_cross_ministry_linking_is_strictly_rejected(): void
    {
        $mainCatA = Category::create([
            'ministry_id' => $this->ministryA->id,
            'name' => 'قطاع الطرق',
            'parent_id' => null,
            'department_id' => null,
        ]);

        // Attempt via Admin to link subcategory to parent in Ministry A and department in Ministry B
        $response = $this->actingAs($this->superAdmin)->post(route('admin.categories.store'), [
            'name' => 'تماس كهربائي في الطريق',
            'parent_id' => $mainCatA->id,
            'department_id' => $this->departmentB1->id, // Belongs to Ministry B!
        ]);

        $response->assertSessionHasErrors('department_id');
        $this->assertDatabaseMissing('categories', ['name' => 'تماس كهربائي في الطريق']);
    }

    /**
     * 4. Unique constraint: main category name is unique within the same ministry.
     */
    public function test_unique_constraint_on_main_categories_in_same_ministry(): void
    {
        Category::create([
            'ministry_id' => $this->ministryA->id,
            'name' => 'البنية التحتية',
            'parent_id' => null,
            'department_id' => null,
        ]);

        // Attempting to create duplicate in same ministry
        $response = $this->actingAs($this->superAdmin)->post(route('admin.categories.store'), [
            'ministry_id' => $this->ministryA->id,
            'name' => 'البنية التحتية',
            'parent_id' => null,
        ]);

        $response->assertSessionHasErrors('name');

        // But allowed in a different ministry
        $responseOther = $this->actingAs($this->superAdmin)->post(route('admin.categories.store'), [
            'ministry_id' => $this->ministryB->id,
            'name' => 'البنية التحتية',
            'parent_id' => null,
        ]);
        $responseOther->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'ministry_id' => $this->ministryB->id,
            'name' => 'البنية التحتية',
        ]);
    }

    /**
     * 5. Unique constraint: subcategory name is unique under the same parent.
     */
    public function test_unique_constraint_on_subcategories_under_same_parent(): void
    {
        $mainCat = Category::create([
            'ministry_id' => $this->ministryA->id,
            'name' => 'قطاع الطرق',
            'parent_id' => null,
        ]);

        Category::create([
            'ministry_id' => $this->ministryA->id,
            'parent_id' => $mainCat->id,
            'department_id' => $this->departmentA1->id,
            'name' => 'حفر إسفلتية',
        ]);

        $response = $this->actingAs($this->superAdmin)->post(route('admin.categories.store'), [
            'parent_id' => $mainCat->id,
            'department_id' => $this->departmentA1->id,
            'name' => 'حفر إسفلتية',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * 6. Deactivating main category automatically hides all its subcategories from API and rejects new complaints.
     */
    public function test_deactivating_main_category_hides_all_its_subcategories_from_api_and_complaints(): void
    {
        $mainCat = Category::create([
            'ministry_id' => $this->ministryA->id,
            'name' => 'قطاع الطرق',
            'parent_id' => null,
            'is_active' => true,
        ]);

        $subCat = Category::create([
            'ministry_id' => $this->ministryA->id,
            'parent_id' => $mainCat->id,
            'department_id' => $this->departmentA1->id,
            'name' => 'هبوط إسفلتي',
            'is_active' => true,
        ]);

        // When active, both appear in API
        $apiResponse = $this->getJson('/api/v1/categories');
        $apiResponse->assertStatus(200);
        $apiResponse->assertJsonFragment(['name' => 'قطاع الطرق']);
        $apiResponse->assertJsonFragment(['name' => 'هبوط إسفلتي']);

        // Deactivate main category
        $mainCat->update(['is_active' => false]);

        // API should now exclude both the main category and its subcategory
        $apiResponseAfter = $this->getJson('/api/v1/categories');
        $apiResponseAfter->assertStatus(200);
        $apiResponseAfter->assertJsonMissing(['name' => 'قطاع الطرق']);
        $apiResponseAfter->assertJsonMissing(['name' => 'هبوط إسفلتي']);

        // Attempting to submit complaint with subcategory should be rejected
        $complaintResponse = $this->actingAs($this->citizen)->postJson('/api/v1/complaints', [
            'category_id' => $subCat->id,
            'title' => 'بلاغ في طريق فرعي',
            'description' => 'وصف المشكلة في الطريق العام لا يقل عن عشرة أحرف',
            'latitude' => 15.3500,
            'longitude' => 44.2000,
        ]);

        $complaintResponse->assertStatus(422);
        $complaintResponse->assertJsonValidationErrors('category_id');
        $this->assertStringContainsString('معطل', $complaintResponse->json('errors.category_id.0'));
    }

    /**
     * 7. API strictly rejects complaints using a main category directly.
     */
    public function test_api_rejects_complaints_using_main_category_directly(): void
    {
        $mainCat = Category::create([
            'ministry_id' => $this->ministryA->id,
            'name' => 'البنية التحتية وشبكة الطرق',
            'parent_id' => null,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->citizen)->postJson('/api/v1/complaints', [
            'category_id' => $mainCat->id,
            'title' => 'محاولة تقديم بلاغ على قطاع عام',
            'description' => 'وصف البلاغ التوضيحي لا يقل عن عشرة أحرف كاملة',
            'latitude' => 15.3500,
            'longitude' => 44.2000,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('category_id');
        $this->assertStringContainsString('رئيسي', $response->json('errors.category_id.0'));
    }

    /**
     * 8. API rejects complaints using inactive subcategory or inactive department.
     */
    public function test_api_rejects_complaints_with_inactive_subcategory_or_inactive_department(): void
    {
        $mainCat = Category::create([
            'ministry_id' => $this->ministryA->id,
            'name' => 'قطاع الطرق',
            'parent_id' => null,
            'is_active' => true,
        ]);

        // A) Inactive subcategory
        $inactiveSub = Category::create([
            'ministry_id' => $this->ministryA->id,
            'parent_id' => $mainCat->id,
            'department_id' => $this->departmentA1->id,
            'name' => 'مشكلة مجمدة مؤقتاً',
            'is_active' => false,
        ]);

        $respA = $this->actingAs($this->citizen)->postJson('/api/v1/complaints', [
            'category_id' => $inactiveSub->id,
            'title' => 'بلاغ تصنيف غير نشط',
            'description' => 'وصف البلاغ التوضيحي لا يقل عن عشرة أحرف كاملة',
            'latitude' => 15.3500,
            'longitude' => 44.2000,
        ]);
        $respA->assertStatus(422);
        $respA->assertJsonValidationErrors('category_id');

        // B) Inactive department
        $inactiveDept = Department::create([
            'ministry_id' => $this->ministryA->id,
            'name' => 'إدارة مغلقة للصيانة',
            'description' => 'معطلة',
            'is_active' => false,
        ]);

        $subWithInactiveDept = Category::create([
            'ministry_id' => $this->ministryA->id,
            'parent_id' => $mainCat->id,
            'department_id' => $inactiveDept->id,
            'name' => 'مشكلة إدارة معطلة',
            'is_active' => true,
        ]);

        $respB = $this->actingAs($this->citizen)->postJson('/api/v1/complaints', [
            'category_id' => $subWithInactiveDept->id,
            'title' => 'بلاغ إدارة معطلة',
            'description' => 'وصف البلاغ التوضيحي لا يقل عن عشرة أحرف كاملة',
            'latitude' => 15.3500,
            'longitude' => 44.2000,
        ]);
        $respB->assertStatus(422);
        $respB->assertJsonValidationErrors('category_id');
        $this->assertStringContainsString('الإدارة', $respB->json('errors.category_id.0'));
    }

    /**
     * 9. Routing engine directs complaint to correct department without random fallback.
     */
    public function test_routing_engine_directs_complaint_to_correct_department_without_random_fallback(): void
    {
        $mainCat = Category::create([
            'ministry_id' => $this->ministryA->id,
            'name' => 'قطاع الطرق',
            'parent_id' => null,
            'is_active' => true,
        ]);

        $subCatFacilities = Category::create([
            'ministry_id' => $this->ministryA->id,
            'parent_id' => $mainCat->id,
            'department_id' => $this->departmentA2->id, // Routed to Facilities!
            'name' => 'تضرر اللوحات والمرافق',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->citizen)->postJson('/api/v1/complaints', [
            'category_id' => $subCatFacilities->id,
            'title' => 'لوحة إرشادية مكسورة في الشارع',
            'description' => 'سقوط اللوحة المرورية يعيق الرؤية عند التقاطع',
            'latitude' => 15.3500,
            'longitude' => 44.2000,
        ]);

        $response->assertStatus(201);
        $complaintId = $response->json('data.id');

        $this->assertDatabaseHas('complaints', [
            'id' => $complaintId,
            'category_id' => $subCatFacilities->id,
            'current_department_id' => $this->departmentA2->id,
        ]);
    }

    /**
     * 10. Updating subcategory department affects only new complaints; historical complaints preserve their department.
     */
    public function test_updating_subcategory_department_affects_only_new_complaints_preserving_historical_records(): void
    {
        $mainCat = Category::create([
            'ministry_id' => $this->ministryA->id,
            'name' => 'قطاع الطرق والمرافق',
            'parent_id' => null,
            'is_active' => true,
        ]);

        $subCat = Category::create([
            'ministry_id' => $this->ministryA->id,
            'parent_id' => $mainCat->id,
            'department_id' => $this->departmentA1->id, // Initially Roads Dept
            'name' => 'صيانة شواخص المنعطفات',
            'is_active' => true,
        ]);

        // Complaint 1 created when assigned to Roads Dept (A1)
        $resp1 = $this->actingAs($this->citizen)->postJson('/api/v1/complaints', [
            'category_id' => $subCat->id,
            'title' => 'بلاغ أول قبل تغيير الإدارة',
            'description' => 'وصف المشكلة الأولى لا يقل عن عشرة أحرف',
            'latitude' => 15.3500,
            'longitude' => 44.2000,
        ]);
        $resp1->assertStatus(201);
        $complaint1Id = $resp1->json('data.id');

        // Admin updates subcategory's responsible department to Facilities Dept (A2)
        $updateResp = $this->actingAs($this->superAdmin)->put(route('admin.categories.update', $subCat), [
            'name' => 'صيانة شواخص المنعطفات والمرافق',
            'department_id' => $this->departmentA2->id, // Reassigned to Facilities
        ]);
        $updateResp->assertRedirect();

        // Complaint 2 created after updating subcategory department
        $resp2 = $this->actingAs($this->citizen)->postJson('/api/v1/complaints', [
            'category_id' => $subCat->id,
            'title' => 'بلاغ ثان بعد تغيير الإدارة',
            'description' => 'وصف المشكلة الثانية لا يقل عن عشرة أحرف',
            'latitude' => 15.3510,
            'longitude' => 44.2010,
        ]);
        $resp2->assertStatus(201);
        $complaint2Id = $resp2->json('data.id');

        // Historical complaint 1 MUST preserve Department A1
        $this->assertDatabaseHas('complaints', [
            'id' => $complaint1Id,
            'current_department_id' => $this->departmentA1->id,
        ]);

        // New complaint 2 MUST be routed to Department A2
        $this->assertDatabaseHas('complaints', [
            'id' => $complaint2Id,
            'current_department_id' => $this->departmentA2->id,
        ]);
    }

    /**
     * 11. Cannot hard delete category with subcategories or complaints, but safe delete succeeds if clean.
     */
    public function test_cannot_delete_category_with_subcategories_or_complaints(): void
    {
        $mainCat = Category::create([
            'ministry_id' => $this->ministryA->id,
            'name' => 'قطاع الاختبار',
            'parent_id' => null,
            'is_active' => true,
        ]);

        $subCat = Category::create([
            'ministry_id' => $this->ministryA->id,
            'parent_id' => $mainCat->id,
            'department_id' => $this->departmentA1->id,
            'name' => 'مشكلة تجريبية',
            'is_active' => true,
        ]);

        // Attempt to delete main category while it has subcategory -> Rejected
        $delMainResp = $this->actingAs($this->superAdmin)->delete(route('admin.categories.destroy', $mainCat));
        $delMainResp->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $mainCat->id]);

        // Create complaint on subCat
        Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $subCat->id,
            'current_department_id' => $this->departmentA1->id,
            'title' => 'بلاغ تجريبي',
            'description' => 'وصف المشكلة التجريبية لا يقل عن عشرة أحرف',
            'status' => 'new',
            'priority' => 'low',
            'latitude' => 15.3500,
            'longitude' => 44.2000,
        ]);

        // Attempt to delete subcategory while it has complaints -> Rejected
        $delSubResp = $this->actingAs($this->superAdmin)->delete(route('admin.categories.destroy', $subCat));
        $delSubResp->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $subCat->id]);

        // Clean category can be deleted safely
        $cleanCat = Category::create([
            'ministry_id' => $this->ministryA->id,
            'parent_id' => null,
            'name' => 'تصنيف فارغ نظيف',
            'is_active' => true,
        ]);

        $delCleanResp = $this->actingAs($this->superAdmin)->delete(route('admin.categories.destroy', $cleanCat));
        $delCleanResp->assertSessionHas('success');
        $this->assertDatabaseMissing('categories', ['id' => $cleanCat->id]);
    }

    /**
     * 12. Total complaints count includes both direct historical complaints and children complaints.
     */
    public function test_total_complaints_count_aggregates_direct_and_children_complaints(): void
    {
        $mainCat = Category::create([
            'ministry_id' => $this->ministryA->id,
            'name' => 'قطاع الطرق المتكامل',
            'parent_id' => null,
            'is_active' => true,
        ]);

        $subCat1 = Category::create([
            'ministry_id' => $this->ministryA->id,
            'parent_id' => $mainCat->id,
            'department_id' => $this->departmentA1->id,
            'name' => 'فرعي 1',
            'is_active' => true,
        ]);

        $subCat2 = Category::create([
            'ministry_id' => $this->ministryA->id,
            'parent_id' => $mainCat->id,
            'department_id' => $this->departmentA2->id,
            'name' => 'فرعي 2',
            'is_active' => true,
        ]);

        // 1 direct complaint on mainCat (historical)
        Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $mainCat->id,
            'current_department_id' => $this->departmentA1->id,
            'title' => 'بلاغ مباشر قديم على القطاع',
            'description' => 'وصف البلاغ التاريخي المباشر لا يقل عن عشرة أحرف',
            'status' => 'resolved',
            'priority' => 'medium',
            'latitude' => 15.3500,
            'longitude' => 44.2000,
        ]);

        // 2 complaints on subCat1
        for ($i = 0; $i < 2; $i++) {
            Complaint::create([
                'citizen_id' => $this->citizen->id,
                'category_id' => $subCat1->id,
                'current_department_id' => $this->departmentA1->id,
                'title' => "بلاغ فرعي 1 رقم {$i}",
                'description' => 'وصف البلاغ الفرعي لا يقل عن عشرة أحرف كاملة',
                'status' => 'new',
                'priority' => 'low',
                'latitude' => 15.3500,
                'longitude' => 44.2000,
            ]);
        }

        // 3 complaints on subCat2
        for ($i = 0; $i < 3; $i++) {
            Complaint::create([
                'citizen_id' => $this->citizen->id,
                'category_id' => $subCat2->id,
                'current_department_id' => $this->departmentA2->id,
                'title' => "بلاغ فرعي 2 رقم {$i}",
                'description' => 'وصف البلاغ الفرعي لا يقل عن عشرة أحرف كاملة',
                'status' => 'new',
                'priority' => 'low',
                'latitude' => 15.3500,
                'longitude' => 44.2000,
            ]);
        }

        // Total for mainCat = 1 direct + 2 subCat1 + 3 subCat2 = 6!
        $this->assertEquals(6, $mainCat->fresh()->total_complaints_count);
        $this->assertEquals(2, $subCat1->fresh()->total_complaints_count);
        $this->assertEquals(3, $subCat2->fresh()->total_complaints_count);
    }
}
