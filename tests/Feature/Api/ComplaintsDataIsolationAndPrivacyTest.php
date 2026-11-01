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
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ComplaintsDataIsolationAndPrivacyTest extends TestCase
{
    use RefreshDatabase;

    protected Ministry $ministryWater;

    protected Department $departmentWater;

    protected Category $categoryWater;

    protected Ministry $ministryWorks;

    protected Department $departmentWorks;

    protected Category $categoryWorks;

    protected Role $citizenRole;

    protected Role $workerRole;

    protected Role $ministryAdminRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(MinistryDepartmentSeeder::class);
        $this->seed(CategorySeeder::class);

        $this->citizenRole = Role::where('name', 'Citizen')->firstOrFail();
        $this->workerRole = Role::where('name', 'Field Worker')->firstOrFail();
        $this->ministryAdminRole = Role::where('name', 'Ministry Admin')->firstOrFail();

        // 1. Ministry of Water & Environment setup
        $this->ministryWater = Ministry::firstOrCreate(
            ['name' => 'وزارة المياه والبيئة'],
            ['code' => 'MOWE', 'is_active' => true]
        );
        $this->departmentWater = Department::firstOrCreate(
            ['name' => 'المؤسسة المحلية للمياه والصرف الصحي', 'ministry_id' => $this->ministryWater->id],
            ['description' => 'إدارة خدمات المياه والصرف الصحي', 'is_active' => true]
        );
        $this->categoryWater = Category::where('department_id', $this->departmentWater->id)
            ->whereNotNull('parent_id')
            ->first() ?? Category::firstOrCreate([
                'name' => 'انكسار خط مياه رئيسي',
                'parent_id' => Category::whereNull('parent_id')->first()->id,
                'department_id' => $this->departmentWater->id,
                'ministry_id' => $this->ministryWater->id,
                'level' => 2,
                'is_active' => true,
            ]);

        // 2. Ministry of Public Works setup
        $this->categoryWorks = Category::whereNotNull('parent_id')
            ->whereNotNull('department_id')
            ->where('department_id', '!=', $this->departmentWater->id)
            ->firstOrFail();
        $this->departmentWorks = $this->categoryWorks->department;
        $this->ministryWorks = $this->departmentWorks->ministry;
    }

    /**
     * Mandatory Multi-User Isolation Test:
     * - Citizen A has 2 complaints.
     * - Citizen B has 3 complaints.
     * - Worker C has 1 old complaint (as citizen) + 1 new complaint (after upgrade) + 1 assigned task on Citizen B's complaint.
     *
     * Expected Results:
     * - Citizen A: strictly their 2 complaints.
     * - Citizen B: strictly their 3 complaints.
     * - Worker C: strictly their 2 personal complaints (assigned task from Citizen B MUST NOT appear).
     * - Worker C field tasks: contains the assigned task from Citizen B.
     */
    public function test_multi_user_complaints_strict_isolation(): void
    {
        // 1. Citizen A
        $citizenA = User::factory()->create(['name' => 'المواطن أ', 'is_active' => true]);
        $citizenA->roles()->sync([$this->citizenRole->id]);

        $complaintA1 = Complaint::create([
            'citizen_id' => $citizenA->id,
            'category_id' => $this->categoryWorks->id,
            'current_department_id' => $this->departmentWorks->id,
            'title' => 'بلاغ المواطن أ رقم 1',
            'description' => 'وصف تفصيلي للبلاغ الأول للمواطن أ',
            'status' => 'new',
            'priority' => 'low',
            'latitude' => 15.350,
            'longitude' => 44.200,
        ]);
        $complaintA2 = Complaint::create([
            'citizen_id' => $citizenA->id,
            'category_id' => $this->categoryWorks->id,
            'current_department_id' => $this->departmentWorks->id,
            'title' => 'بلاغ المواطن أ رقم 2',
            'description' => 'وصف تفصيلي للبلاغ الثاني للمواطن أ',
            'status' => 'under_review',
            'priority' => 'medium',
            'latitude' => 15.351,
            'longitude' => 44.201,
        ]);

        // 2. Citizen B
        $citizenB = User::factory()->create(['name' => 'المواطن ب', 'is_active' => true]);
        $citizenB->roles()->sync([$this->citizenRole->id]);

        $complaintB1 = Complaint::create([
            'citizen_id' => $citizenB->id,
            'category_id' => $this->categoryWorks->id,
            'current_department_id' => $this->departmentWorks->id,
            'title' => 'بلاغ المواطن ب رقم 1',
            'description' => 'وصف تفصيلي للبلاغ الأول للمواطن ب',
            'status' => 'new',
            'priority' => 'high',
            'latitude' => 15.352,
            'longitude' => 44.202,
        ]);
        $complaintB2 = Complaint::create([
            'citizen_id' => $citizenB->id,
            'category_id' => $this->categoryWorks->id,
            'current_department_id' => $this->departmentWorks->id,
            'title' => 'بلاغ المواطن ب رقم 2',
            'description' => 'وصف تفصيلي للبلاغ الثاني للمواطن ب',
            'status' => 'in_progress',
            'priority' => 'urgent',
            'latitude' => 15.353,
            'longitude' => 44.203,
        ]);
        $complaintB3 = Complaint::create([
            'citizen_id' => $citizenB->id,
            'category_id' => $this->categoryWorks->id,
            'current_department_id' => $this->departmentWorks->id,
            'title' => 'بلاغ المواطن ب رقم 3',
            'description' => 'وصف تفصيلي للبلاغ الثالث للمواطن ب',
            'status' => 'resolved',
            'priority' => 'medium',
            'latitude' => 15.354,
            'longitude' => 44.204,
        ]);

        // 3. Worker C
        $workerC = User::factory()->create([
            'name' => 'العامل ج',
            'department_id' => $this->departmentWorks->id,
            'is_active' => true,
        ]);
        // Historical complaint filed before promotion
        $complaintC_Old = Complaint::create([
            'citizen_id' => $workerC->id,
            'category_id' => $this->categoryWorks->id,
            'current_department_id' => $this->departmentWorks->id,
            'title' => 'بلاغ العامل ج القديم قبل الترقية',
            'description' => 'وصف البلاغ الشخصي للعامل قبل ترقيته',
            'status' => 'new',
            'priority' => 'low',
            'latitude' => 15.355,
            'longitude' => 44.205,
        ]);
        // Promote Worker C
        $workerC->roles()->sync([$this->workerRole->id]);

        // New complaint filed after promotion
        $complaintC_New = Complaint::create([
            'citizen_id' => $workerC->id,
            'category_id' => $this->categoryWorks->id,
            'current_department_id' => $this->departmentWorks->id,
            'title' => 'بلاغ العامل ج الجديد بعد الترقية',
            'description' => 'وصف البلاغ الشخصي للعامل بعد ترقيته',
            'status' => 'new',
            'priority' => 'urgent',
            'latitude' => 15.356,
            'longitude' => 44.206,
        ]);

        // Assign Complaint B2 (authored by Citizen B) to Worker C as a field task
        $admin = User::factory()->create([
            'department_id' => $this->departmentWorks->id,
            'is_active' => true,
        ]);
        $admin->roles()->sync([$this->ministryAdminRole->id]);

        $task = FieldAssignment::create([
            'complaint_id' => $complaintB2->id,
            'worker_id' => $workerC->id,
            'assigned_by' => $admin->id,
            'status' => 'in_progress',
        ]);

        // --- VERIFY CITIZEN A ---
        Sanctum::actingAs($citizenA);
        $respA = $this->getJson('/api/v1/complaints');
        $respA->assertStatus(200);
        $idsA = collect($respA->json('data'))->pluck('id')->toArray();
        $this->assertCount(2, $idsA);
        $this->assertEqualsCanonicalizing([$complaintA1->id, $complaintA2->id], $idsA);

        // --- VERIFY CITIZEN B ---
        Sanctum::actingAs($citizenB);
        $respB = $this->getJson('/api/v1/complaints');
        $respB->assertStatus(200);
        $idsB = collect($respB->json('data'))->pluck('id')->toArray();
        $this->assertCount(3, $idsB);
        $this->assertEqualsCanonicalizing([$complaintB1->id, $complaintB2->id, $complaintB3->id], $idsB);

        // --- VERIFY WORKER C ---
        Sanctum::actingAs($workerC);
        $respC = $this->getJson('/api/v1/complaints');
        $respC->assertStatus(200);
        $idsC = collect($respC->json('data'))->pluck('id')->toArray();
        $this->assertCount(2, $idsC);
        // Only old and new personal complaints!
        $this->assertEqualsCanonicalizing([$complaintC_Old->id, $complaintC_New->id], $idsC);
        // Assigned complaint from Citizen B MUST NOT be in "My Complaints"
        $this->assertNotContains($complaintB2->id, $idsC);

        // --- VERIFY WORKER C FIELD ASSIGNMENTS ---
        $respTasks = $this->getJson('/api/v1/field-assignments');
        $respTasks->assertStatus(200);
        $taskIds = collect($respTasks->json('data'))->pluck('id')->toArray();
        $this->assertContains($task->id, $taskIds);
    }

    /**
     * Mandatory Test Case: User promoted to Ministry Admin of Ministry of Water and Environment.
     *
     * Steps:
     * 1. Create User.
     * 2. User registers 2 complaints.
     * 3. Multiple other complaints are created by different users in Ministry of Water & Environment.
     * 4. Upgrade User to Ministry Admin and bind account to Ministry of Water & Environment.
     * 5. User calls "My Complaints" (GET /api/v1/complaints).
     *
     * Expected Results:
     * - "My Complaints" displays strictly their 2 personal complaints.
     * - Complaints of other citizens in the Ministry of Water and Environment DO NOT appear in "My Complaints".
     * - Web Ministry Dashboard for Ministry of Water displays all ministry complaints.
     */
    public function test_user_promoted_to_ministry_admin_only_sees_personal_complaints_in_my_complaints(): void
    {
        // 1. Create User as Citizen initially
        $user = User::factory()->create([
            'name' => 'مشرف وزارة تجريبي',
            'email' => 'admin.water@bader.test',
            'department_id' => null,
            'is_active' => true,
        ]);
        $user->roles()->sync([$this->citizenRole->id]);

        // 2. User files 2 personal complaints
        $myComplaint1 = Complaint::create([
            'citizen_id' => $user->id,
            'category_id' => $this->categoryWorks->id,
            'current_department_id' => $this->departmentWorks->id,
            'title' => 'بلاغي الشخصي رقم 1 صيانة رصيف',
            'description' => 'وصف تفصيلي للبلاغ الشخصي الأول',
            'status' => 'new',
            'priority' => 'low',
            'latitude' => 15.350,
            'longitude' => 44.200,
        ]);
        $myComplaint2 = Complaint::create([
            'citizen_id' => $user->id,
            'category_id' => $this->categoryWater->id,
            'current_department_id' => $this->departmentWater->id,
            'title' => 'بلاغي الشخصي رقم 2 تسريب مياه أمام منزلي',
            'description' => 'تسريب مياه نظيفة في الشارع يحتاج إصلاح',
            'status' => 'under_review',
            'priority' => 'high',
            'latitude' => 15.351,
            'longitude' => 44.201,
        ]);

        // 3. Other users file 3 different complaints in Ministry of Water & Environment
        $otherCitizen1 = User::factory()->create(['name' => 'مواطن مائي 1', 'is_active' => true]);
        $otherCitizen2 = User::factory()->create(['name' => 'مواطن مائي 2', 'is_active' => true]);

        $waterComplaint1 = Complaint::create([
            'citizen_id' => $otherCitizen1->id,
            'category_id' => $this->categoryWater->id,
            'current_department_id' => $this->departmentWater->id,
            'title' => 'انفجار أنبوب مياه رئيسي في حي الصافية',
            'description' => 'تدفق كميات هائلة من المياه في الشارع العام',
            'status' => 'new',
            'priority' => 'urgent',
            'latitude' => 15.360,
            'longitude' => 44.210,
        ]);
        $waterComplaint2 = Complaint::create([
            'citizen_id' => $otherCitizen1->id,
            'category_id' => $this->categoryWater->id,
            'current_department_id' => $this->departmentWater->id,
            'title' => 'انقطاع المياه عن حي الروضة',
            'description' => 'انقطاع تام لشبكة المياه لثلاثة أيام متواصلة',
            'status' => 'under_review',
            'priority' => 'medium',
            'latitude' => 15.361,
            'longitude' => 44.211,
        ]);
        $waterComplaint3 = Complaint::create([
            'citizen_id' => $otherCitizen2->id,
            'category_id' => $this->categoryWater->id,
            'current_department_id' => $this->departmentWater->id,
            'title' => 'انسداد خط صرف صحي',
            'description' => 'طفح مياه الصرف الصحي بجوار المسجد',
            'status' => 'assigned',
            'priority' => 'high',
            'latitude' => 15.362,
            'longitude' => 44.212,
        ]);

        // 4. Upgrade User to Ministry Admin of Ministry of Water & Environment
        $user->update(['department_id' => $this->departmentWater->id]);
        $user->roles()->sync([$this->ministryAdminRole->id]);

        $this->assertEquals($this->departmentWater->id, $user->fresh()->department_id);
        $this->assertEquals($this->ministryWater->id, $user->fresh()->department->ministry_id);
        $this->assertTrue($user->fresh()->hasRole('Ministry Admin'));

        // 5. User requests "My Complaints" via Mobile API
        Sanctum::actingAs($user);
        $response = $this->getJson('/api/v1/complaints');
        $response->assertStatus(200);

        $returnedIds = collect($response->json('data'))->pluck('id')->toArray();

        // STRICT ASSERTION: ONLY their 2 personal complaints
        $this->assertCount(2, $returnedIds, 'سجل بلاغاتي يجب ألا يعرض سوى بلاغي المستخدم الشخصيين فقط!');
        $this->assertEqualsCanonicalizing([$myComplaint1->id, $myComplaint2->id], $returnedIds);

        // STRICT SECURITY ASSERTION: Ministry complaints authored by others MUST NOT be present
        $this->assertNotContains($waterComplaint1->id, $returnedIds);
        $this->assertNotContains($waterComplaint2->id, $returnedIds);
        $this->assertNotContains($waterComplaint3->id, $returnedIds);

        // 6. Verify Ministry Web Dashboard still displays all ministry complaints
        $webResponse = $this->actingAs($user)->get(route('ministry.complaints.index'));
        $webResponse->assertStatus(200);
        $dashboardComplaints = $webResponse->viewData('complaints');

        $dashboardIds = collect($dashboardComplaints->items())->pluck('id')->toArray();
        $this->assertContains($waterComplaint1->id, $dashboardIds);
        $this->assertContains($waterComplaint2->id, $dashboardIds);
        $this->assertContains($waterComplaint3->id, $dashboardIds);
        $this->assertContains($myComplaint2->id, $dashboardIds); // Complaint #2 is in Water ministry
        $this->assertNotContains($myComplaint1->id, $dashboardIds); // Complaint #1 is in Works ministry
    }

    /**
     * Test Complaint Details Security (ComplaintPolicy::view):
     * - User cannot view details of other user's complaint via GET /api/v1/complaints/{id}.
     * - Field worker cannot view unassigned complaint of other user.
     * - Field worker CAN view assigned complaint.
     */
    public function test_complaint_details_endpoint_security_and_authorization(): void
    {
        $citizenA = User::factory()->create(['is_active' => true]);
        $citizenA->roles()->sync([$this->citizenRole->id]);

        $citizenB = User::factory()->create(['is_active' => true]);
        $citizenB->roles()->sync([$this->citizenRole->id]);

        $worker = User::factory()->create([
            'department_id' => $this->departmentWorks->id,
            'is_active' => true,
        ]);
        $worker->roles()->sync([$this->workerRole->id]);

        $complaintA = Complaint::create([
            'citizen_id' => $citizenA->id,
            'category_id' => $this->categoryWorks->id,
            'current_department_id' => $this->departmentWorks->id,
            'title' => 'بلاغ المواطن أ السري',
            'description' => 'تفاصيل خاصة يجب عدم كشفها',
            'status' => 'new',
            'priority' => 'high',
            'latitude' => 15.350,
            'longitude' => 44.200,
        ]);

        // 1. Citizen A can view their own complaint
        Sanctum::actingAs($citizenA);
        $this->getJson("/api/v1/complaints/{$complaintA->id}")->assertStatus(200);

        // 2. Citizen B CANNOT view Citizen A's complaint (403 Forbidden)
        Sanctum::actingAs($citizenB);
        $this->getJson("/api/v1/complaints/{$complaintA->id}")->assertStatus(403);

        // 3. Worker CANNOT view unassigned complaint of Citizen A (403 Forbidden)
        Sanctum::actingAs($worker);
        $this->getJson("/api/v1/complaints/{$complaintA->id}")->assertStatus(403);

        // 4. Assign complaint to Worker -> Worker can now view it
        $admin = User::factory()->create(['department_id' => $this->departmentWorks->id, 'is_active' => true]);
        $admin->roles()->sync([$this->ministryAdminRole->id]);

        FieldAssignment::create([
            'complaint_id' => $complaintA->id,
            'worker_id' => $worker->id,
            'assigned_by' => $admin->id,
            'status' => 'in_progress',
        ]);

        Sanctum::actingAs($worker);
        $this->getJson("/api/v1/complaints/{$complaintA->id}")->assertStatus(200);
    }

    /**
     * Test Pagination, Filters, and Search strictly respect user ownership without data leakage.
     */
    public function test_pagination_and_filters_preserve_data_isolation(): void
    {
        $citizen = User::factory()->create(['is_active' => true]);
        $citizen->roles()->sync([$this->citizenRole->id]);

        $other = User::factory()->create(['is_active' => true]);
        $other->roles()->sync([$this->citizenRole->id]);

        // Create 20 complaints for other citizen matching 'مياه'
        for ($i = 0; $i < 20; $i++) {
            Complaint::create([
                'citizen_id' => $other->id,
                'category_id' => $this->categoryWorks->id,
                'current_department_id' => $this->departmentWorks->id,
                'title' => "بلاغ الآخرين مياه $i",
                'description' => 'وصف يحتوي على كلمة مياه',
                'status' => 'new',
                'priority' => 'low',
                'latitude' => 15.350,
                'longitude' => 44.200,
            ]);
        }

        // Create 1 complaint for citizen matching 'مياه'
        $myComplaint = Complaint::create([
            'citizen_id' => $citizen->id,
            'category_id' => $this->categoryWorks->id,
            'current_department_id' => $this->departmentWorks->id,
            'title' => 'بلاغي أنا فقط مياه نظيفة',
            'description' => 'وصف خاص ببلاغي الشخصي',
            'status' => 'new',
            'priority' => 'high',
            'latitude' => 15.350,
            'longitude' => 44.200,
        ]);

        Sanctum::actingAs($citizen);

        // Query with search
        $searchResp = $this->getJson('/api/v1/complaints?search=مياه');
        $searchResp->assertStatus(200);

        $ids = collect($searchResp->json('data'))->pluck('id')->toArray();
        $this->assertCount(1, $ids);
        $this->assertEquals([$myComplaint->id], $ids);

        // Query page 2 -> must be empty for this citizen
        $page2Resp = $this->getJson('/api/v1/complaints?page=2');
        $page2Resp->assertStatus(200);
        $this->assertEmpty($page2Resp->json('data'));
    }
}
