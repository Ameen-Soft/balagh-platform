<?php

namespace Tests\Feature\Ministry;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\ComplaintTransfer;
use App\Models\Department;
use App\Models\FieldAssignment;
use App\Models\Ministry;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceDepartmentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Ministry $ministryA;
    protected Ministry $ministryB;
    protected Department $deptA1;
    protected Department $deptA2;
    protected Department $deptB1;
    protected User $adminA;
    protected User $adminB;
    protected User $citizen;
    protected User $workerA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $ministryAdminRole = Role::where('name', 'Ministry Admin')->firstOrFail();
        $fieldWorkerRole = Role::where('name', 'Field Worker')->firstOrFail();
        $citizenRole = Role::where('name', 'Citizen')->firstOrFail();

        // Ministry A
        $this->ministryA = Ministry::create([
            'name' => 'وزارة الأشغال العامة والطرق',
            'code' => 'MOPW',
            'logo' => 'logos/default.png',
            'contact_email' => 'mopw@example.test',
            'is_active' => true,
        ]);
        $this->deptA1 = Department::create([
            'ministry_id' => $this->ministryA->id,
            'name' => 'ديوان عام الوزارة',
            'description' => 'المقر الرئيسي والمشرف',
            'is_active' => true,
        ]);
        $this->deptA2 = Department::create([
            'ministry_id' => $this->ministryA->id,
            'name' => 'إدارة صيانة الطرق والجسور',
            'description' => 'المسؤولة عن الطرق المعبدة',
            'is_active' => true,
        ]);

        // Ministry B
        $this->ministryB = Ministry::create([
            'name' => 'وزارة المياه والبيئة',
            'code' => 'MOWE',
            'logo' => 'logos/default.png',
            'contact_email' => 'mowe@example.test',
            'is_active' => true,
        ]);
        $this->deptB1 = Department::create([
            'ministry_id' => $this->ministryB->id,
            'name' => 'المؤسسة العامة للمياه',
            'description' => 'إدارة إمدادات المياه',
            'is_active' => true,
        ]);

        // Admin A
        $this->adminA = User::factory()->create([
            'department_id' => $this->deptA1->id,
            'is_active' => true,
        ]);
        $this->adminA->roles()->attach($ministryAdminRole);

        // Admin B
        $this->adminB = User::factory()->create([
            'department_id' => $this->deptB1->id,
            'is_active' => true,
        ]);
        $this->adminB->roles()->attach($ministryAdminRole);

        // Worker A
        $this->workerA = User::factory()->create([
            'department_id' => $this->deptA2->id,
            'is_active' => true,
        ]);
        $this->workerA->roles()->attach($fieldWorkerRole);

        // Citizen
        $this->citizen = User::factory()->create(['is_active' => true]);
        $this->citizen->roles()->attach($citizenRole);
    }

    public function test_ministry_admin_sees_only_their_ministry_departments_on_index(): void
    {
        $response = $this->actingAs($this->adminA)->get('/ministry/departments');

        $response->assertOk();
        $response->assertSee('ديوان عام الوزارة');
        $response->assertSee('إدارة صيانة الطرق والجسور');
        $response->assertDontSee('المؤسسة العامة للمياه');
    }

    public function test_ministry_admin_can_view_department_details_with_all_metrics(): void
    {
        $response = $this->actingAs($this->adminA)->get("/ministry/departments/{$this->deptA2->id}");

        $response->assertOk();
        $response->assertSee($this->deptA2->name);
        $response->assertSee($this->deptA2->description);
        $response->assertSee('الموظفون الميدانيون التابعون للإدارة');
        $response->assertSee($this->workerA->name);
    }

    public function test_ministry_admin_cannot_view_department_of_another_ministry(): void
    {
        $response = $this->actingAs($this->adminA)->get("/ministry/departments/{$this->deptB1->id}");

        $response->assertForbidden();
    }

    public function test_ministry_admin_can_update_department_details_successfully(): void
    {
        $response = $this->actingAs($this->adminA)->put("/ministry/departments/{$this->deptA2->id}", [
            'name' => 'إدارة شبكات الطرق الحديثة',
            'description' => 'وصف تفصيلي محدث لاختصاص الإدارة',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('departments', [
            'id' => $this->deptA2->id,
            'name' => 'إدارة شبكات الطرق الحديثة',
            'description' => 'وصف تفصيلي محدث لاختصاص الإدارة',
        ]);
    }

    public function test_ministry_admin_cannot_update_department_of_another_ministry(): void
    {
        $response = $this->actingAs($this->adminA)->put("/ministry/departments/{$this->deptB1->id}", [
            'name' => 'محاولة اختراق تعديل إدارة أخرى',
            'description' => 'وصف غير مصرح به',
        ]);

        $response->assertForbidden();
    }

    public function test_cannot_update_department_to_a_duplicate_name_within_same_ministry(): void
    {
        $response = $this->actingAs($this->adminA)->put("/ministry/departments/{$this->deptA2->id}", [
            'name' => 'ديوان عام الوزارة', // Already exists in Ministry A
            'description' => 'وصف مكرر',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_ministry_admin_can_toggle_active_state_of_department(): void
    {
        $this->assertTrue($this->deptA2->is_active);

        // Deactivate
        $response = $this->actingAs($this->adminA)->post("/ministry/departments/{$this->deptA2->id}/toggle-active");
        $response->assertSessionHas('success');
        $this->assertFalse($this->deptA2->fresh()->is_active);

        // Reactivate
        $response = $this->actingAs($this->adminA)->post("/ministry/departments/{$this->deptA2->id}/toggle-active");
        $response->assertSessionHas('success');
        $this->assertTrue($this->deptA2->fresh()->is_active);
    }

    public function test_ministry_admin_cannot_toggle_active_of_another_ministry_department(): void
    {
        $response = $this->actingAs($this->adminA)->post("/ministry/departments/{$this->deptB1->id}/toggle-active");

        $response->assertForbidden();
    }

    public function test_deactivated_department_is_excluded_from_new_subcategory_assignment(): void
    {
        $this->deptA2->update(['is_active' => false]);

        $mainCat = Category::create([
            'ministry_id' => $this->ministryA->id,
            'name' => 'قطاع النقل العام',
            'level' => 1,
            'is_active' => true,
        ]);

        // Attempt to create a subcategory linked to inactive deptA2
        $response = $this->actingAs($this->adminA)->post('/ministry/categories', [
            'name' => 'تصنيف تحت إدارة معطلة',
            'parent_id' => $mainCat->id,
            'department_id' => $this->deptA2->id,
            'description' => 'وصف اختباري',
        ]);

        $response->assertSessionHasErrors('department_id');
    }

    public function test_deactivated_department_is_excluded_from_complaint_transfers(): void
    {
        $this->deptA2->update(['is_active' => false]);

        $cat = Category::create([
            'ministry_id' => $this->ministryA->id,
            'department_id' => $this->deptA1->id,
            'name' => 'تصنيف ديوان',
            'level' => 1,
            'is_active' => true,
        ]);

        $complaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $cat->id,
            'current_department_id' => $this->deptA1->id,
            'title' => 'بلاغ تجريبي للتحويل',
            'description' => 'وصف البلاغ التجريبي بدقة لا يقل عن عشرة أحرف',
            'status' => 'new',
            'priority' => 'medium',
            'latitude' => 15.3500,
            'longitude' => 44.2000,
        ]);

        // Attempt to transfer to inactive department deptA2
        $response = $this->actingAs($this->adminA)->post("/ministry/complaints/{$complaint->id}/transfer", [
            'to_department_id' => $this->deptA2->id,
            'reason' => 'تحويل لإدارة مغلقة لاختبار الحظر',
        ]);

        $response->assertSessionHasErrors('to_department_id');
    }

    public function test_deactivating_department_preserves_historical_complaints_and_does_not_change_current_department_id(): void
    {
        $cat = Category::create([
            'ministry_id' => $this->ministryA->id,
            'department_id' => $this->deptA2->id,
            'name' => 'هندسة الجسور',
            'level' => 1,
            'is_active' => true,
        ]);

        $complaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $cat->id,
            'current_department_id' => $this->deptA2->id,
            'title' => 'بلاغ تاريخي مهم',
            'description' => 'وصف البلاغ التاريخي المسجل قبل تعطيل الإدارة الخدمية',
            'status' => 'in_progress',
            'priority' => 'high',
            'latitude' => 15.3500,
            'longitude' => 44.2000,
        ]);

        // Deactivate department
        $this->actingAs($this->adminA)->post("/ministry/departments/{$this->deptA2->id}/toggle-active");

        $this->assertFalse($this->deptA2->fresh()->is_active);
        // Historical complaint remains with exact current_department_id intact
        $this->assertEquals($this->deptA2->id, $complaint->fresh()->current_department_id);
    }

    public function test_can_delete_department_when_it_has_zero_relations(): void
    {
        $emptyDept = Department::create([
            'ministry_id' => $this->ministryA->id,
            'name' => 'إدارة جديدة فارغة بالخطأ',
            'description' => 'بدون أي سجلات',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminA)->delete("/ministry/departments/{$emptyDept->id}");

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('departments', ['id' => $emptyDept->id]);
    }

    public function test_cannot_delete_department_linked_to_complaints(): void
    {
        $cat = Category::create([
            'ministry_id' => $this->ministryA->id,
            'department_id' => $this->deptA2->id,
            'name' => 'صيانة الأسفلت',
            'level' => 1,
            'is_active' => true,
        ]);

        Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $cat->id,
            'current_department_id' => $this->deptA2->id,
            'title' => 'بلاغ يمنع الحذف',
            'description' => 'وصف البلاغ التوضيحي الذي يحفظ التاريخ التشغيلي',
            'status' => 'new',
            'priority' => 'medium',
            'latitude' => 15.3500,
            'longitude' => 44.2000,
        ]);

        $response = $this->actingAs($this->adminA)->delete("/ministry/departments/{$this->deptA2->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('departments', ['id' => $this->deptA2->id]);
    }

    public function test_cannot_delete_department_linked_to_categories(): void
    {
        // Dept without complaints but with a category
        $emptyDept = Department::create([
            'ministry_id' => $this->ministryA->id,
            'name' => 'إدارة لها تصنيف فقط',
            'description' => 'وصف',
            'is_active' => true,
        ]);

        Category::create([
            'ministry_id' => $this->ministryA->id,
            'department_id' => $emptyDept->id,
            'name' => 'تصنيف مرتبط',
            'level' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminA)->delete("/ministry/departments/{$emptyDept->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('departments', ['id' => $emptyDept->id]);
    }

    public function test_cannot_delete_department_linked_to_users_or_workers(): void
    {
        // DeptA2 already has $this->workerA
        $response = $this->actingAs($this->adminA)->delete("/ministry/departments/{$this->deptA2->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('departments', ['id' => $this->deptA2->id]);
    }

    public function test_cannot_delete_department_assigned_to_current_ministry_admin(): void
    {
        // DeptA1 has adminA assigned to it
        $response = $this->actingAs($this->adminA)->delete("/ministry/departments/{$this->deptA1->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('departments', ['id' => $this->deptA1->id]);
    }

    public function test_cannot_delete_department_linked_to_complaint_transfers(): void
    {
        $transferDept = Department::create([
            'ministry_id' => $this->ministryA->id,
            'name' => 'إدارة محول منها بلاغ سابق',
            'description' => 'وصف',
            'is_active' => true,
        ]);

        $cat = Category::create([
            'ministry_id' => $this->ministryA->id,
            'department_id' => $this->deptA1->id,
            'name' => 'تصنيف عام',
            'level' => 1,
            'is_active' => true,
        ]);

        $c = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $cat->id,
            'current_department_id' => $this->deptA1->id,
            'title' => 'بلاغ محول',
            'description' => 'تفاصيل البلاغ المحول تاريخياً من إدارة سابقة',
            'status' => 'new',
            'priority' => 'low',
            'latitude' => 15.3500,
            'longitude' => 44.2000,
        ]);

        ComplaintTransfer::create([
            'complaint_id' => $c->id,
            'from_department_id' => $transferDept->id,
            'to_department_id' => $this->deptA1->id,
            'reason' => 'إحالة للاختصاص',
            'transferred_by' => $this->adminA->id,
        ]);

        $response = $this->actingAs($this->adminA)->delete("/ministry/departments/{$transferDept->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('departments', ['id' => $transferDept->id]);
    }

    public function test_cannot_delete_department_linked_to_projects(): void
    {
        $projDept = Department::create([
            'ministry_id' => $this->ministryA->id,
            'name' => 'إدارة مشاريع الجسور',
            'description' => 'وصف',
            'is_active' => true,
        ]);

        Project::create([
            'title' => 'مشروع جسر الصداقة',
            'description' => 'تطوير الجسر الميداني',
            'department_id' => $projDept->id,
            'target_amount' => 500000,
            'current_amount' => 10000,
            'status' => 'active',
            'latitude' => 15.3500,
            'longitude' => 44.2000,
            'created_by' => $this->adminA->id,
        ]);

        $response = $this->actingAs($this->adminA)->delete("/ministry/departments/{$projDept->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('departments', ['id' => $projDept->id]);
    }

    public function test_unauthorized_users_citizen_and_worker_cannot_manage_departments(): void
    {
        $this->actingAs($this->citizen)->get('/ministry/departments')->assertForbidden();
        $this->actingAs($this->workerA)->get('/ministry/departments')->assertForbidden();

        $this->actingAs($this->citizen)->post('/ministry/departments', [
            'name' => 'إدارة مواطن غير مسموحة',
            'description' => 'وصف',
        ])->assertForbidden();
    }
}
