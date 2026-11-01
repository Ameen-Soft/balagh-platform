<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\Department;
use App\Models\FieldAssignment;
use App\Models\Ministry;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\MinistryDepartmentSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WorkerComplaintLifecycleAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $citizen;

    protected User $ministryAdmin;

    protected User $otherCitizen;

    protected Category $category;

    protected Department $department;

    protected Ministry $ministry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(MinistryDepartmentSeeder::class);
        $this->seed(CategorySeeder::class);

        // Active subcategory with parent and department
        $this->category = Category::whereNotNull('parent_id')
            ->whereNotNull('department_id')
            ->where('is_active', true)
            ->whereHas('parent', fn ($q) => $q->where('is_active', true))
            ->whereHas('department', fn ($q) => $q->where('is_active', true))
            ->firstOrFail();

        $this->department = $this->category->department;
        $this->ministry = $this->department->ministry;

        // Ministry Admin
        $adminRole = Role::where('name', 'Ministry Admin')->firstOrFail();
        $this->ministryAdmin = User::factory()->create([
            'department_id' => $this->department->id,
            'is_active' => true,
        ]);
        $this->ministryAdmin->roles()->sync([$adminRole->id]);

        // Citizen
        $citizenRole = Role::where('name', 'Citizen')->firstOrFail();
        $this->citizen = User::factory()->create([
            'department_id' => null,
            'is_active' => true,
        ]);
        $this->citizen->roles()->sync([$citizenRole->id]);

        // Another Citizen
        $this->otherCitizen = User::factory()->create([
            'department_id' => null,
            'is_active' => true,
        ]);
        $this->otherCitizen->roles()->sync([$citizenRole->id]);
    }

    /**
     * Case 1: Citizen before promotion can create complaints and see them in "My Complaints".
     */
    public function test_citizen_before_promotion_can_create_and_view_complaints(): void
    {
        Sanctum::actingAs($this->citizen);

        // Create Complaint 1
        $response1 = $this->postJson('/api/v1/complaints', [
            'title' => 'بلاغ مواطن رقم 1 حفرة عميقة',
            'description' => 'يوجد حفرة عميقة في وسط الطريق الرئيسي تعيق حركة السير.',
            'category_id' => $this->category->id,
            'latitude' => 15.369445,
            'longitude' => 44.191007,
            'priority' => 'high',
        ]);
        $response1->assertStatus(201);
        $complaint1Id = $response1->json('data.id');

        // Create Complaint 2
        $response2 = $this->postJson('/api/v1/complaints', [
            'title' => 'بلاغ مواطن رقم 2 تراكم نفايات',
            'description' => 'تراكم كميات كبيرة من المخلفات بجوار مدرسة الأمل.',
            'category_id' => $this->category->id,
            'latitude' => 15.370000,
            'longitude' => 44.192000,
            'priority' => 'medium',
        ]);
        $response2->assertStatus(201);
        $complaint2Id = $response2->json('data.id');

        // View My Complaints
        $listResponse = $this->getJson('/api/v1/complaints');
        $listResponse->assertStatus(200);

        $ids = collect($listResponse->json('data'))->pluck('id')->toArray();
        $this->assertContains($complaint1Id, $ids);
        $this->assertContains($complaint2Id, $ids);
    }

    /**
     * Case 2: Citizen promoted to Worker maintains user ID, historical complaints,
     * and can view them in "My Complaints" without 403 Forbidden error.
     */
    public function test_promoted_worker_maintains_identity_and_can_view_historical_complaints(): void
    {
        // 1. Citizen creates 2 complaints
        Sanctum::actingAs($this->citizen);
        $complaint1 = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->department->id,
            'title' => 'بلاغ مواطن قديم 1',
            'description' => 'وصف البلاغ الأول قبل ترقية الحساب إلى عامل.',
            'status' => 'new',
            'priority' => 'high',
            'latitude' => 15.350000,
            'longitude' => 44.200000,
        ]);

        $complaint2 = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->department->id,
            'title' => 'بلاغ مواطن قديم 2',
            'description' => 'وصف البلاغ الثاني قبل ترقية الحساب إلى عامل.',
            'status' => 'under_review',
            'priority' => 'medium',
            'latitude' => 15.351000,
            'longitude' => 44.201000,
        ]);

        $originalUserId = $this->citizen->id;

        // 2. Promote using official Ministry Worker Management route
        $promoteResponse = $this->actingAs($this->ministryAdmin)->post(
            route('ministry.workers.promote', $this->citizen->id),
            ['department_id' => $this->department->id]
        );
        $promoteResponse->assertRedirect();
        $promoteResponse->assertSessionHas('success');

        $this->citizen->refresh();

        // 3. Assert user ID is preserved and role is now Field Worker
        $this->assertEquals($originalUserId, $this->citizen->id);
        $this->assertTrue($this->citizen->hasRole('Field Worker'));
        $this->assertFalse($this->citizen->hasRole('Citizen'));
        $this->assertEquals($this->department->id, $this->citizen->department_id);

        // 4. Assert complaints still belong to the same citizen_id
        $this->assertEquals($originalUserId, $complaint1->fresh()->citizen_id);
        $this->assertEquals($originalUserId, $complaint2->fresh()->citizen_id);

        // 5. Worker requests "My Complaints" via API
        Sanctum::actingAs($this->citizen);
        $listResponse = $this->getJson('/api/v1/complaints');

        // MUST be 200 OK (NOT 403 Forbidden)
        $listResponse->assertStatus(200);

        $returnedIds = collect($listResponse->json('data'))->pluck('id')->toArray();
        $this->assertContains($complaint1->id, $returnedIds);
        $this->assertContains($complaint2->id, $returnedIds);
    }

    /**
     * Case 3: Promoted worker can create a new complaint from mobile, with auto-routing,
     * and see both old and new complaints in "My Complaints".
     */
    public function test_promoted_worker_can_create_new_complaint_and_see_it_in_history(): void
    {
        Storage::fake('public');

        // Promote citizen
        $fieldWorkerRole = Role::where('name', 'Field Worker')->firstOrFail();
        $this->citizen->update(['department_id' => $this->department->id]);
        $this->citizen->roles()->sync([$fieldWorkerRole->id]);

        // Historical complaint created before promotion
        $oldComplaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->department->id,
            'title' => 'بلاغ قديم قبل الترقية',
            'description' => 'وصف البلاغ القديم الذي تم إنشاؤه مسبقاً.',
            'status' => 'new',
            'priority' => 'low',
            'latitude' => 15.350000,
            'longitude' => 44.200000,
        ]);

        // Worker creates new complaint with attachment
        Sanctum::actingAs($this->citizen);
        $fakeImage = UploadedFile::fake()->create('road_damage.jpg', 100, 'image/jpeg');

        $storeResponse = $this->postJson('/api/v1/complaints', [
            'title' => 'بلاغ جديد من الموظف الميداني',
            'description' => 'رصد هبوط في الإسفلت أثناء الجولة الميدانية يحتاج صيانة فورية.',
            'category_id' => $this->category->id,
            'latitude' => 15.360000,
            'longitude' => 44.210000,
            'priority' => 'urgent',
            'is_public' => true,
            'attachments' => [$fakeImage],
        ]);

        // MUST be 201 Created (NOT 403 Forbidden)
        $storeResponse->assertStatus(201)
            ->assertJsonPath('success', true);

        $newComplaintId = $storeResponse->json('data.id');
        $newComplaint = Complaint::find($newComplaintId);

        $this->assertNotNull($newComplaint);
        $this->assertEquals($this->citizen->id, $newComplaint->citizen_id);
        $this->assertEquals($this->department->id, $newComplaint->current_department_id);
        $this->assertCount(1, $newComplaint->attachments);

        // Fetch My Complaints: both old and new must appear
        $listResponse = $this->getJson('/api/v1/complaints');
        $listResponse->assertStatus(200);

        $returnedIds = collect($listResponse->json('data'))->pluck('id')->toArray();
        $this->assertContains($oldComplaint->id, $returnedIds);
        $this->assertContains($newComplaintId, $returnedIds);
    }

    /**
     * Case 4: Ownership protection & RBAC security:
     * - Worker cannot see other users' complaints in "My Complaints".
     * - Worker cannot spoof ownership by passing another user's ID.
     * - Worker cannot access web admin or ministry dashboards.
     * - Worker cannot perform administrative operational status updates.
     */
    public function test_ownership_protection_and_rbac_boundaries(): void
    {
        $fieldWorkerRole = Role::where('name', 'Field Worker')->firstOrFail();
        $this->citizen->update(['department_id' => $this->department->id]);
        $this->citizen->roles()->sync([$fieldWorkerRole->id]);

        // Complaint created by other citizen in the SAME department/ministry
        $otherComplaint = Complaint::create([
            'citizen_id' => $this->otherCitizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->department->id,
            'title' => 'بلاغ مواطن آخر في نفس الإدارة',
            'description' => 'بلاغ خاص بمواطن آخر يجب ألا يظهر في سجل بلاغاتي الخاص بالعامل.',
            'status' => 'new',
            'priority' => 'medium',
            'latitude' => 15.355000,
            'longitude' => 44.205000,
        ]);

        // Own complaint
        $ownComplaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->department->id,
            'title' => 'بلاغي الشخصي كعامل',
            'description' => 'هذا البلاغ أنشأته أنا شخصياً ويجب أن يظهر لي فقط.',
            'status' => 'new',
            'priority' => 'low',
            'latitude' => 15.350000,
            'longitude' => 44.200000,
        ]);

        Sanctum::actingAs($this->citizen);

        // 1. Worker index only returns own complaints
        $listResponse = $this->getJson('/api/v1/complaints');
        $listResponse->assertStatus(200);

        $returnedIds = collect($listResponse->json('data'))->pluck('id')->toArray();
        $this->assertContains($ownComplaint->id, $returnedIds);
        $this->assertNotContains($otherComplaint->id, $returnedIds);

        // 2. Worker cannot spoof citizen_id during creation
        $spoofResponse = $this->postJson('/api/v1/complaints', [
            'citizen_id' => $this->otherCitizen->id,
            'title' => 'محاولة تزوير ملكية البلاغ',
            'description' => 'محاولة لتسجيل البلاغ باسم مستخدم آخر عبر إرسال معرّفه.',
            'category_id' => $this->category->id,
            'latitude' => 15.360000,
            'longitude' => 44.210000,
        ]);
        $spoofResponse->assertStatus(201);

        $createdComplaint = Complaint::find($spoofResponse->json('data.id'));
        // Must belong to the authenticated worker, NOT the other citizen!
        $this->assertEquals($this->citizen->id, $createdComplaint->citizen_id);
        $this->assertNotEquals($this->otherCitizen->id, $createdComplaint->citizen_id);

        // 3. Worker cannot access admin/ministry web dashboards
        $this->actingAs($this->citizen)->get(route('admin.dashboard'))->assertStatus(403);
        $this->actingAs($this->citizen)->get(route('ministry.dashboard'))->assertStatus(403);
        $this->actingAs($this->citizen)->get(route('ministry.complaints.index'))->assertStatus(403);

        // 4. Worker cannot perform administrative operational status updates via API
        Sanctum::actingAs($this->citizen);
        $updateStatusResponse = $this->patchJson("/api/v1/complaints/{$otherComplaint->id}/status", [
            'status' => 'rejected',
            'notes' => 'محاولة غير مصرح بها لرفض البلاغ',
        ]);
        $updateStatusResponse->assertStatus(403);
    }

    /**
     * Case 5: Worker field duties remain intact:
     * - Field assignments list, start, geofence check, completion.
     * - Personal complaint filing does NOT conflict with field duties.
     */
    public function test_worker_field_duties_remain_fully_functional(): void
    {
        $fieldWorkerRole = Role::where('name', 'Field Worker')->firstOrFail();
        $this->citizen->update(['department_id' => $this->department->id]);
        $this->citizen->roles()->sync([$fieldWorkerRole->id]);

        // A complaint filed by another citizen
        $complaintToWorkOn = Complaint::create([
            'citizen_id' => $this->otherCitizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->department->id,
            'title' => 'مهمة إصلاح حفرة إسفلتية',
            'description' => 'مهمة مسندة للعامل الميداني لإصلاح الحفرة.',
            'status' => 'under_review',
            'priority' => 'high',
            'latitude' => 15.350000,
            'longitude' => 44.200000,
        ]);

        // 1. Admin assigns the complaint to the worker
        $assignResponse = $this->actingAs($this->ministryAdmin, 'sanctum')
            ->postJson("/api/v1/complaints/{$complaintToWorkOn->id}/assign", [
                'user_id' => $this->citizen->id,
                'notes' => 'يرجى الانتقال للموقع وتوثيق المشكلة بالصور.',
            ]);
        $assignResponse->assertStatus(201);

        $assignment = FieldAssignment::where('complaint_id', $complaintToWorkOn->id)->firstOrFail();
        $this->assertEquals($this->citizen->id, $assignment->worker_id);

        // 2. Worker fetches assignments list
        Sanctum::actingAs($this->citizen);
        $assignmentsResponse = $this->getJson('/api/v1/field-assignments');
        $assignmentsResponse->assertStatus(200);

        $assignmentIds = collect($assignmentsResponse->json('data'))->pluck('id')->toArray();
        $this->assertContains($assignment->id, $assignmentIds);

        // 3. Worker starts task
        $startResponse = $this->postJson("/api/v1/field-assignments/{$assignment->id}/start");
        $startResponse->assertStatus(200);
        $this->assertEquals('in_progress', $assignment->fresh()->status);

        // 4. Worker verifies location inside geofence (exact coordinates)
        $verifyResponse = $this->postJson("/api/v1/field-assignments/{$assignment->id}/verify-location", [
            'latitude' => 15.350000,
            'longitude' => 44.200000,
        ]);
        $verifyResponse->assertStatus(200)
            ->assertJsonPath('data.is_within_range', true);

        // 5. Worker completes assignment with evidence
        $fakeProof = UploadedFile::fake()->create('resolved.jpg', 100, 'image/jpeg');
        $completeResponse = $this->postJson("/api/v1/field-assignments/{$assignment->id}/complete", [
            'latitude' => 15.350000,
            'longitude' => 44.200000,
            'report' => 'تم ردم الحفرة الإسفلتية وإعادة سفلتة الموقع بنجاح.',
            'evidence_attachments' => [$fakeProof],
        ]);
        $completeResponse->assertStatus(200);

        $this->assertEquals('completed', $assignment->fresh()->status);
        $this->assertEquals('resolved', $complaintToWorkOn->fresh()->status);
    }
}
