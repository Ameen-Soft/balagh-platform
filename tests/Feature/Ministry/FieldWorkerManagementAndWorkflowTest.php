<?php

namespace Tests\Feature\Ministry;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\Department;
use App\Models\FieldAssignment;
use App\Models\Ministry;
use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FieldWorkerManagementAndWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $ministryAdmin;
    protected User $otherMinistryAdmin;
    protected User $fieldWorker;
    protected User $otherFieldWorker;
    protected User $citizen;
    protected Department $department;
    protected Department $otherDepartment;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

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
            'description' => 'صيانة شبكات الطرق والجسور',
            'is_active' => true,
        ]);

        $otherMinistry = Ministry::create([
            'name' => 'وزارة المياه والبيئة',
            'code' => 'MOWE',
            'logo' => 'logos/mowe.png',
            'contact_email' => 'mowe@example.test',
            'is_active' => true,
        ]);
        $this->otherDepartment = Department::create([
            'ministry_id' => $otherMinistry->id,
            'name' => 'مؤسسة المياه',
            'description' => 'إمدادات المياه والصرف الصحي',
            'is_active' => true,
        ]);

        $this->category = Category::create([
            'department_id' => $this->department->id,
            'name' => 'صيانة الأسفلت',
            'description' => 'أعمال صيانة وردم الحفر الإسفلتية',
            'level' => 1,
            'is_active' => true,
        ]);

        $ministryAdminRole = Role::where('name', 'Ministry Admin')->firstOrFail();
        $fieldWorkerRole = Role::where('name', 'Field Worker')->firstOrFail();
        $citizenRole = Role::where('name', 'Citizen')->firstOrFail();

        // 1. Ministry Admin
        $this->ministryAdmin = User::factory()->create([
            'department_id' => $this->department->id,
            'is_active' => true,
        ]);
        $this->ministryAdmin->roles()->sync([$ministryAdminRole->id]);

        // 2. Other Ministry Admin
        $this->otherMinistryAdmin = User::factory()->create([
            'department_id' => $this->otherDepartment->id,
            'is_active' => true,
        ]);
        $this->otherMinistryAdmin->roles()->sync([$ministryAdminRole->id]);

        // 3. Field Worker
        $this->fieldWorker = User::factory()->create([
            'department_id' => $this->department->id,
            'is_active' => true,
        ]);
        $this->fieldWorker->roles()->sync([$fieldWorkerRole->id]);

        // 4. Other Field Worker
        $this->otherFieldWorker = User::factory()->create([
            'department_id' => $this->otherDepartment->id,
            'is_active' => true,
        ]);
        $this->otherFieldWorker->roles()->sync([$fieldWorkerRole->id]);

        // 5. Citizen
        $this->citizen = User::factory()->create([
            'department_id' => null,
            'is_active' => true,
        ]);
        $this->citizen->roles()->sync([$citizenRole->id]);
    }

    /*
    |--------------------------------------------------------------------------
    | 1. Field Worker Management Tests (Promote / Demote / Scoping)
    |--------------------------------------------------------------------------
    */

    public function test_ministry_admin_can_view_workers_index_and_see_department_workers(): void
    {
        $response = $this->actingAs($this->ministryAdmin)->get(route('ministry.workers.index'));

        $response->assertStatus(200)
            ->assertSee('إدارة الموظفين الميدانيين')
            ->assertSee($this->fieldWorker->name)
            ->assertDontSee($this->otherFieldWorker->name);
    }

    public function test_ministry_admin_can_promote_citizen_to_field_worker(): void
    {
        $newCitizen = User::factory()->create(['department_id' => null, 'is_active' => true]);
        $citizenRole = Role::where('name', 'Citizen')->firstOrFail();
        $newCitizen->roles()->sync([$citizenRole->id]);

        $response = $this->actingAs($this->ministryAdmin)->post(
            route('ministry.workers.promote', $newCitizen->id)
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $refreshed = $newCitizen->fresh();
        $this->assertTrue($refreshed->hasRole('Field Worker'));
        $this->assertEquals($this->department->id, $refreshed->department_id);

        // Verify notification sent to promoted user
        $this->assertDatabaseHas('notifications', [
            'user_id' => $newCitizen->id,
            'type' => 'worker_promoted',
        ]);
    }

    public function test_ministry_admin_cannot_promote_another_admin(): void
    {
        $response = $this->actingAs($this->ministryAdmin)->post(
            route('ministry.workers.promote', $this->otherMinistryAdmin->id)
        );

        $response->assertSessionHas('error');
        $this->assertFalse($this->otherMinistryAdmin->fresh()->hasRole('Field Worker'));
    }

    public function test_ministry_admin_cannot_demote_worker_from_another_department(): void
    {
        $response = $this->actingAs($this->ministryAdmin)->post(
            route('ministry.workers.demote', $this->otherFieldWorker->id)
        );

        $response->assertStatus(403);
        $this->assertTrue($this->otherFieldWorker->fresh()->hasRole('Field Worker'));
    }

    public function test_ministry_admin_cannot_demote_worker_with_active_assignments(): void
    {
        $complaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->department->id,
            'title' => 'هبوط إسفلتي',
            'description' => 'هبوط في الشارع العام',
            'status' => 'in_progress',
            'priority' => 'high',
            'latitude' => 15.3500,
            'longitude' => 44.2000,
        ]);

        FieldAssignment::create([
            'complaint_id' => $complaint->id,
            'worker_id' => $this->fieldWorker->id,
            'assigned_by' => $this->ministryAdmin->id,
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        $response = $this->actingAs($this->ministryAdmin)->post(
            route('ministry.workers.demote', $this->fieldWorker->id)
        );

        $response->assertSessionHas('error');
        $this->assertTrue($this->fieldWorker->fresh()->hasRole('Field Worker'));
        $this->assertEquals($this->department->id, $this->fieldWorker->fresh()->department_id);
    }

    public function test_ministry_admin_can_demote_worker_without_active_assignments(): void
    {
        $response = $this->actingAs($this->ministryAdmin)->post(
            route('ministry.workers.demote', $this->fieldWorker->id)
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $refreshed = $this->fieldWorker->fresh();
        $this->assertFalse($refreshed->hasRole('Field Worker'));
        $this->assertTrue($refreshed->hasRole('Citizen'));
        $this->assertNull($refreshed->department_id);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->fieldWorker->id,
            'type' => 'worker_demoted',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 2. Assignment Directly In-Progress Tests
    |--------------------------------------------------------------------------
    */

    public function test_assigning_worker_transitions_complaint_and_task_directly_to_in_progress(): void
    {
        $complaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->department->id,
            'title' => 'ماس كهربائي في عمود إنارة',
            'description' => 'يسبب خطورة على المارة',
            'status' => 'under_review',
            'priority' => 'urgent',
            'latitude' => 15.3500,
            'longitude' => 44.2000,
        ]);

        $response = $this->actingAs($this->ministryAdmin)->post(
            route('ministry.complaints.assign_worker', $complaint->id),
            [
                'worker_id' => $this->fieldWorker->id,
                'notes' => 'التوجه الفوري وتأمين الموقع وإجراء الإصلاح.',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Complaint must be in_progress directly
        $this->assertEquals('in_progress', $complaint->fresh()->status);

        // Assignment must exist with in_progress status and started_at set
        $assignment = FieldAssignment::where('complaint_id', $complaint->id)->first();
        $this->assertNotNull($assignment);
        $this->assertEquals('in_progress', $assignment->status);
        $this->assertNotNull($assignment->started_at);
        $this->assertEquals($this->fieldWorker->id, $assignment->worker_id);

        // Notifications sent to worker and citizen
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->fieldWorker->id,
            'type' => 'task_assigned',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->citizen->id,
            'type' => 'complaint_status_changed',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 3. Complaint Rejection Requirements & State Transitions
    |--------------------------------------------------------------------------
    */

    public function test_rejection_requires_mandatory_notes_reason(): void
    {
        $complaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->department->id,
            'title' => 'طلب غير مطابق',
            'description' => 'طلب صيانة لأرض خاصة',
            'status' => 'under_review',
            'priority' => 'low',
            'latitude' => 15.3500,
            'longitude' => 44.2000,
        ]);

        // Attempt rejection without notes
        $response = $this->actingAs($this->ministryAdmin)->post(
            route('ministry.complaints.update_status', $complaint->id),
            [
                'status' => 'rejected',
                'notes' => '',
            ]
        );

        $response->assertSessionHasErrors(['notes']);
        $this->assertNotEquals('rejected', $complaint->fresh()->status);

        // Successful rejection with valid reason
        $validResponse = $this->actingAs($this->ministryAdmin)->post(
            route('ministry.complaints.update_status', $complaint->id),
            [
                'status' => 'rejected',
                'notes' => 'الموقع ملكية خاصة ولا يخضع لاختصاص وزارة الأشغال العامة.',
            ]
        );

        $validResponse->assertRedirect();
        $this->assertEquals('rejected', $complaint->fresh()->status);

        // Verify citizen received status change notification
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->citizen->id,
            'type' => 'complaint_status_changed',
        ]);
    }

    public function test_invalid_lifecycle_transition_is_rejected_server_side(): void
    {
        $complaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->department->id,
            'title' => 'بلاغ جديد',
            'description' => 'وصف البلاغ الجديد',
            'status' => 'new',
            'priority' => 'medium',
            'latitude' => 15.3500,
            'longitude' => 44.2000,
        ]);

        // Direct leap from 'new' to 'closed' is disallowed by lifecycle transition map
        Sanctum::actingAs($this->ministryAdmin);
        $response = $this->patchJson("/api/v1/complaints/{$complaint->id}/status", [
            'status' => 'closed',
            'notes' => 'محاولة إغلاق غير مصرح بها',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);

        $this->assertEquals('new', $complaint->fresh()->status);
    }

    /*
    |--------------------------------------------------------------------------
    | 4. Task Execution Constraints (Worker identity, Geofence, Requirements)
    |--------------------------------------------------------------------------
    */

    public function test_worker_cannot_complete_task_assigned_to_another_worker(): void
    {
        $complaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->department->id,
            'title' => 'كسر في ماسورة رئيسية',
            'description' => 'تدفق مياه في الشارع',
            'status' => 'in_progress',
            'priority' => 'high',
            'latitude' => 15.369445,
            'longitude' => 44.191006,
        ]);

        $assignment = FieldAssignment::create([
            'complaint_id' => $complaint->id,
            'worker_id' => $this->fieldWorker->id,
            'assigned_by' => $this->ministryAdmin->id,
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        // Other worker attempts completion
        Sanctum::actingAs($this->otherFieldWorker);
        $response = $this->postJson("/api/v1/field-assignments/{$assignment->id}/complete", [
            'report' => 'محاولة إتمام من موظف غير مسند إليه التكليف',
            'latitude' => 15.369445,
            'longitude' => 44.191006,
        ]);

        // Policy or Service blocks unauthorized worker
        $this->assertTrue(in_array($response->status(), [403, 422]));
        $this->assertEquals('in_progress', $assignment->fresh()->status);
        $this->assertEquals('in_progress', $complaint->fresh()->status);
    }

    public function test_worker_cannot_complete_task_outside_geofence(): void
    {
        $complaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->department->id,
            'title' => 'ردم حفرة في شارع تعز',
            'description' => 'الحفرة تعيق حركة السير',
            'status' => 'in_progress',
            'priority' => 'medium',
            'latitude' => 15.369445,
            'longitude' => 44.191006,
        ]);

        $assignment = FieldAssignment::create([
            'complaint_id' => $complaint->id,
            'worker_id' => $this->fieldWorker->id,
            'assigned_by' => $this->ministryAdmin->id,
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        Sanctum::actingAs($this->fieldWorker);
        $response = $this->postJson("/api/v1/field-assignments/{$assignment->id}/complete", [
            'report' => 'تم التواجد وردم الحفرة وتأمين مسار الطريق بالكامل',
            'latitude' => 15.500000, // > 15 km away
            'longitude' => 44.300000,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['coordinates']);

        $this->assertEquals('in_progress', $assignment->fresh()->status);
    }

    public function test_worker_can_complete_task_inside_geofence_and_sets_complaint_to_resolved_not_closed(): void
    {
        Storage::fake('public');

        $complaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->department->id,
            'title' => 'إصلاح كسر في رصيف المشاة',
            'description' => 'تضرر في الرصيف العام أمام مدرسة',
            'status' => 'in_progress',
            'priority' => 'medium',
            'latitude' => 15.369445,
            'longitude' => 44.191006,
        ]);

        $assignment = FieldAssignment::create([
            'complaint_id' => $complaint->id,
            'worker_id' => $this->fieldWorker->id,
            'assigned_by' => $this->ministryAdmin->id,
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        Sanctum::actingAs($this->fieldWorker);
        $evidenceFile = UploadedFile::fake()->create('fixed_pavement.jpg', 150, 'image/jpeg');

        $response = $this->postJson("/api/v1/field-assignments/{$assignment->id}/complete", [
            'report' => 'تم النزول الميداني وإعادة بناء الرصيف وإصلاح الأضرار بالكامل وفق المعايير.',
            'latitude' => 15.369450, // ~1 meter away
            'longitude' => 44.191010,
            'attachments' => [$evidenceFile],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        // Assignment is completed
        $this->assertEquals('completed', $assignment->fresh()->status);
        $this->assertNotNull($assignment->fresh()->completed_at);

        // Complaint must be 'resolved' (NOT 'closed'!)
        $this->assertEquals('resolved', $complaint->fresh()->status);

        // Notification sent to citizen
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->citizen->id,
            'type' => 'complaint_resolved',
        ]);
    }
}
