<?php

namespace Tests\Feature\Ministry;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\Department;
use App\Models\FieldAssignment;
use App\Models\Ministry;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FieldWorkerExtendedManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Ministry $ministryA;
    protected Ministry $ministryB;
    protected Department $deptA1;
    protected Department $deptA2;
    protected Department $deptB1;
    protected User $adminA;
    protected User $adminB;
    protected User $workerA1;
    protected User $workerA2;
    protected User $workerB;
    protected User $citizen;

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
            'description' => 'المقر الرئيسي',
            'is_active' => true,
        ]);
        $this->deptA2 = Department::create([
            'ministry_id' => $this->ministryA->id,
            'name' => 'إدارة صيانة الطرق',
            'description' => 'إدارة ميدانية تابعة لنفس الوزارة',
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
            'name' => 'مؤسسة المياه',
            'description' => 'إدارة تابعة لوزارة ب',
            'is_active' => true,
        ]);

        // Admins
        $this->adminA = User::factory()->create([
            'name' => 'مشرف وزارة أ',
            'department_id' => $this->deptA1->id,
            'is_active' => true,
        ]);
        $this->adminA->roles()->attach($ministryAdminRole);

        $this->adminB = User::factory()->create([
            'name' => 'مشرف وزارة ب',
            'department_id' => $this->deptB1->id,
            'is_active' => true,
        ]);
        $this->adminB->roles()->attach($ministryAdminRole);

        // Workers for Ministry A across different departments
        $this->workerA1 = User::factory()->create([
            'name' => 'موظف ميداني أ1 (ديوان)',
            'email' => 'worker.a1@example.test',
            'phone' => '777000001',
            'national_id' => '1000000001',
            'department_id' => $this->deptA1->id,
            'is_active' => true,
        ]);
        $this->workerA1->roles()->attach($fieldWorkerRole);

        $this->workerA2 = User::factory()->create([
            'name' => 'موظف ميداني أ2 (طرق)',
            'email' => 'worker.a2@example.test',
            'phone' => '777000002',
            'national_id' => '1000000002',
            'department_id' => $this->deptA2->id,
            'is_active' => true,
        ]);
        $this->workerA2->roles()->attach($fieldWorkerRole);

        // Worker for Ministry B
        $this->workerB = User::factory()->create([
            'name' => 'موظف ميداني وزارة ب',
            'email' => 'worker.b@example.test',
            'phone' => '777000009',
            'national_id' => '1000000009',
            'department_id' => $this->deptB1->id,
            'is_active' => true,
        ]);
        $this->workerB->roles()->attach($fieldWorkerRole);

        // Citizen
        $this->citizen = User::factory()->create(['is_active' => true]);
        $this->citizen->roles()->attach($citizenRole);
    }

    public function test_ministry_admin_sees_workers_from_all_departments_of_their_ministry(): void
    {
        $response = $this->actingAs($this->adminA)->get('/ministry/workers');

        $response->assertOk();
        // Sees both workers of ministry A across deptA1 and deptA2
        $response->assertSee('موظف ميداني أ1 (ديوان)');
        $response->assertSee('موظف ميداني أ2 (طرق)');
        // Does NOT see worker of ministry B
        $response->assertDontSee('موظف ميداني وزارة ب');
    }

    public function test_ministry_admin_can_filter_workers_by_specific_department(): void
    {
        // Filter by deptA2
        $response = $this->actingAs($this->adminA)->get("/ministry/workers?department_id={$this->deptA2->id}");

        $response->assertOk();
        $response->assertSee('موظف ميداني أ2 (طرق)');
        $response->assertDontSee('موظف ميداني أ1 (ديوان)');
        $response->assertDontSee('موظف ميداني وزارة ب');
    }

    public function test_ministry_admin_can_create_new_field_worker_directly(): void
    {
        $response = $this->actingAs($this->adminA)->post('/ministry/workers', [
            'name' => 'مهندس ميداني جديد',
            'email' => 'new.worker@example.test',
            'phone' => '777888999',
            'national_id' => '1234567890',
            'department_id' => $this->deptA2->id,
            'password' => 'SecurePass123!',
        ]);

        $response->assertRedirect('/ministry/workers');
        $response->assertSessionHas('success');

        $newWorker = User::where('email', 'new.worker@example.test')->first();
        $this->assertNotNull($newWorker);
        $this->assertEquals('مهندس ميداني جديد', $newWorker->name);
        $this->assertEquals($this->deptA2->id, $newWorker->department_id);
        $this->assertTrue($newWorker->hasRole('Field Worker'));
        $this->assertTrue($newWorker->is_active);
        $this->assertTrue(Hash::check('SecurePass123!', $newWorker->password));
    }

    public function test_cannot_create_worker_with_duplicate_email_or_phone_or_national_id(): void
    {
        // Duplicate email
        $responseEmail = $this->actingAs($this->adminA)->post('/ministry/workers', [
            'name' => 'موظف مكرر',
            'email' => $this->workerA1->email,
            'phone' => '777555444',
            'department_id' => $this->deptA1->id,
            'password' => 'SecurePass123!',
        ]);
        $responseEmail->assertSessionHasErrors('email');

        // Duplicate phone
        $responsePhone = $this->actingAs($this->adminA)->post('/ministry/workers', [
            'name' => 'موظف مكرر الهاتف',
            'email' => 'unique.email@example.test',
            'phone' => $this->workerA1->phone,
            'department_id' => $this->deptA1->id,
            'password' => 'SecurePass123!',
        ]);
        $responsePhone->assertSessionHasErrors('phone');

        // Duplicate national_id
        $responseId = $this->actingAs($this->adminA)->post('/ministry/workers', [
            'name' => 'موظف مكرر الهوية',
            'email' => 'unique2@example.test',
            'phone' => '777333222',
            'national_id' => $this->workerA1->national_id,
            'department_id' => $this->deptA1->id,
            'password' => 'SecurePass123!',
        ]);
        $responseId->assertSessionHasErrors('national_id');
    }

    public function test_cannot_create_worker_under_inactive_department_or_another_ministry(): void
    {
        // Inactive department
        $inactiveDept = Department::create([
            'ministry_id' => $this->ministryA->id,
            'name' => 'إدارة معطلة',
            'description' => 'وصف',
            'is_active' => false,
        ]);

        $responseInactive = $this->actingAs($this->adminA)->post('/ministry/workers', [
            'name' => 'موظف في إدارة معطلة',
            'email' => 'w.inactive@example.test',
            'phone' => '777111222',
            'department_id' => $inactiveDept->id,
            'password' => 'SecurePass123!',
        ]);
        $responseInactive->assertSessionHasErrors('department_id');

        // Department from another ministry (Ministry B)
        $responseForeign = $this->actingAs($this->adminA)->post('/ministry/workers', [
            'name' => 'موظف في وزارة أخرى',
            'email' => 'w.foreign@example.test',
            'phone' => '777111333',
            'department_id' => $this->deptB1->id,
            'password' => 'SecurePass123!',
        ]);
        $responseForeign->assertSessionHasErrors('department_id');
    }

    public function test_ministry_admin_can_view_worker_details_profile_and_tasks(): void
    {
        $response = $this->actingAs($this->adminA)->get("/ministry/workers/{$this->workerA1->id}");

        $response->assertOk();
        $response->assertSee($this->workerA1->name);
        $response->assertSee($this->workerA1->phone);
        $response->assertSee('سجل المهام الميدانية والتكليفات');
    }

    public function test_ministry_admin_cannot_view_worker_of_another_ministry(): void
    {
        $response = $this->actingAs($this->adminA)->get("/ministry/workers/{$this->workerB->id}");

        $response->assertForbidden();
    }

    public function test_ministry_admin_can_update_worker_details(): void
    {
        $response = $this->actingAs($this->adminA)->put("/ministry/workers/{$this->workerA1->id}", [
            'name' => 'اسم الموظف المحدث',
            'email' => 'updated.worker@example.test',
            'phone' => '777999111',
            'national_id' => '1000000001',
            'department_id' => $this->deptA1->id,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $this->workerA1->id,
            'name' => 'اسم الموظف المحدث',
            'email' => 'updated.worker@example.test',
            'phone' => '777999111',
        ]);
    }

    public function test_ministry_admin_cannot_update_worker_of_another_ministry(): void
    {
        $response = $this->actingAs($this->adminA)->put("/ministry/workers/{$this->workerB->id}", [
            'name' => 'محاولة تعديل موظف وزارة ب',
            'email' => 'hacked@example.test',
            'phone' => '777999888',
            'department_id' => $this->deptB1->id,
        ]);

        $response->assertForbidden();
    }

    public function test_cannot_transfer_worker_to_another_department_if_worker_has_active_tasks(): void
    {
        $cat = Category::create([
            'ministry_id' => $this->ministryA->id,
            'department_id' => $this->deptA1->id,
            'name' => 'تصنيف للطرق',
            'level' => 1,
            'is_active' => true,
        ]);

        $complaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $cat->id,
            'current_department_id' => $this->deptA1->id,
            'title' => 'بلاغ بمهمة نشطة',
            'description' => 'وصف البلاغ التوضيحي الذي يتضمن مهمة نشطة',
            'status' => 'in_progress',
            'priority' => 'medium',
            'latitude' => 15.3500,
            'longitude' => 44.2000,
        ]);

        FieldAssignment::create([
            'complaint_id' => $complaint->id,
            'worker_id' => $this->workerA1->id,
            'assigned_by' => $this->adminA->id,
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        // Attempt to move workerA1 from deptA1 to deptA2 while having active task
        $response = $this->actingAs($this->adminA)->put("/ministry/workers/{$this->workerA1->id}", [
            'name' => $this->workerA1->name,
            'email' => $this->workerA1->email,
            'phone' => $this->workerA1->phone,
            'department_id' => $this->deptA2->id,
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals($this->deptA1->id, $this->workerA1->fresh()->department_id);
    }

    public function test_can_transfer_worker_to_another_department_if_worker_has_no_active_tasks(): void
    {
        $this->assertEquals($this->deptA1->id, $this->workerA1->department_id);

        $response = $this->actingAs($this->adminA)->put("/ministry/workers/{$this->workerA1->id}", [
            'name' => $this->workerA1->name,
            'email' => $this->workerA1->email,
            'phone' => $this->workerA1->phone,
            'department_id' => $this->deptA2->id,
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals($this->deptA2->id, $this->workerA1->fresh()->department_id);
    }

    public function test_deactivating_worker_revokes_current_sanctum_tokens_immediately(): void
    {
        // Issue token to worker
        $token = $this->workerA1->createToken('test_worker_token');
        $this->assertDatabaseCount('personal_access_tokens', 1);

        // Deactivate worker
        $response = $this->actingAs($this->adminA)->post("/ministry/workers/{$this->workerA1->id}/toggle-active");

        $response->assertSessionHas('success');
        $this->assertFalse($this->workerA1->fresh()->is_active);
        // All Sanctum tokens should be purged immediately upon deactivation
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_deactivated_worker_is_rejected_on_api_login(): void
    {
        $this->workerA1->update([
            'password' => Hash::make('WorkerPassword123!'),
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $this->workerA1->email,
            'password' => 'WorkerPassword123!',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
        $this->assertStringContainsString('تعطيل', $response->json('errors.email.0'));
    }

    public function test_deactivated_worker_with_existing_token_is_blocked_by_active_user_middleware(): void
    {
        $token = $this->workerA1->createToken('active_token')->plainTextToken;

        // Manually set is_active to false in DB
        $this->workerA1->update(['is_active' => false]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/field-assignments');

        $response->assertStatus(403);
        $this->assertStringContainsString('تعطيل', $response->json('message'));
    }

    public function test_deactivated_worker_cannot_be_assigned_new_complaints(): void
    {
        $this->workerA1->update(['is_active' => false]);

        $cat = Category::create([
            'ministry_id' => $this->ministryA->id,
            'department_id' => $this->deptA1->id,
            'name' => 'طرق عامة',
            'level' => 1,
            'is_active' => true,
        ]);

        $complaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $cat->id,
            'current_department_id' => $this->deptA1->id,
            'title' => 'بلاغ لاختبار إسناد موظف معطل',
            'description' => 'وصف البلاغ بدقة لا تقل عن عشرة أحرف',
            'status' => 'new',
            'priority' => 'medium',
            'latitude' => 15.3500,
            'longitude' => 44.2000,
        ]);

        // Attempt to assign complaint to inactive workerA1
        $response = $this->actingAs($this->adminA)->post("/ministry/complaints/{$complaint->id}/assign", [
            'worker_id' => $this->workerA1->id,
            'notes' => 'تكليف تجريبي',
        ]);

        $response->assertSessionHasErrors('user_id');
    }

    public function test_reactivating_worker_allows_normal_login_and_task_assignment(): void
    {
        $this->workerA1->update([
            'password' => Hash::make('WorkerPassword123!'),
            'is_active' => false,
        ]);

        // Reactivate
        $this->actingAs($this->adminA)->post("/ministry/workers/{$this->workerA1->id}/toggle-active");
        $this->assertTrue($this->workerA1->fresh()->is_active);

        // Login succeeds
        $loginResp = $this->postJson('/api/v1/auth/login', [
            'email' => $this->workerA1->email,
            'password' => 'WorkerPassword123!',
        ]);
        $loginResp->assertOk();
        $this->assertNotEmpty($loginResp->json('data.token'));
    }

    public function test_can_delete_worker_when_they_have_zero_history(): void
    {
        $cleanWorker = User::factory()->create([
            'name' => 'موظف مضاف بالخطأ بدون مهام',
            'email' => 'clean.worker@example.test',
            'department_id' => $this->deptA1->id,
            'is_active' => true,
        ]);
        $cleanWorker->roles()->attach(Role::where('name', 'Field Worker')->first());

        $response = $this->actingAs($this->adminA)->delete("/ministry/workers/{$cleanWorker->id}");

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $cleanWorker->id]);
    }

    public function test_cannot_delete_worker_with_past_field_assignments(): void
    {
        $cat = Category::create([
            'ministry_id' => $this->ministryA->id,
            'department_id' => $this->deptA1->id,
            'name' => 'تصنيف هندسي',
            'level' => 1,
            'is_active' => true,
        ]);

        $complaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $cat->id,
            'current_department_id' => $this->deptA1->id,
            'title' => 'بلاغ مكتمل بمهمة سابقة',
            'description' => 'وصف البلاغ الذي نفذه الموظف الميداني سابقاً بنجاح',
            'status' => 'resolved',
            'priority' => 'medium',
            'latitude' => 15.3500,
            'longitude' => 44.2000,
        ]);

        FieldAssignment::create([
            'complaint_id' => $complaint->id,
            'worker_id' => $this->workerA1->id,
            'assigned_by' => $this->adminA->id,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($this->adminA)->delete("/ministry/workers/{$this->workerA1->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->workerA1->id]);
    }

    public function test_cannot_delete_worker_with_filed_complaints(): void
    {
        $cat = Category::create([
            'ministry_id' => $this->ministryA->id,
            'department_id' => $this->deptA1->id,
            'name' => 'تصنيف شكوى',
            'level' => 1,
            'is_active' => true,
        ]);

        Complaint::create([
            'citizen_id' => $this->workerA1->id, // worker also filed a complaint
            'category_id' => $cat->id,
            'current_department_id' => $this->deptA1->id,
            'title' => 'بلاغ أنشأه الموظف بنفسه',
            'description' => 'وصف تفصيلي للبلاغ لحفظ السجل التاريخي',
            'status' => 'new',
            'priority' => 'low',
            'latitude' => 15.3500,
            'longitude' => 44.2000,
        ]);

        $response = $this->actingAs($this->adminA)->delete("/ministry/workers/{$this->workerA1->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->workerA1->id]);
    }

    public function test_deactivating_worker_does_not_break_or_deactivate_other_users_or_admins(): void
    {
        // Deactivate workerA1
        $this->actingAs($this->adminA)->post("/ministry/workers/{$this->workerA1->id}/toggle-active");

        $this->assertFalse($this->workerA1->fresh()->is_active);
        // AdminA remains active
        $this->assertTrue($this->adminA->fresh()->is_active);
        // WorkerA2 remains active
        $this->assertTrue($this->workerA2->fresh()->is_active);
        // Citizen remains active
        $this->assertTrue($this->citizen->fresh()->is_active);
    }
}
