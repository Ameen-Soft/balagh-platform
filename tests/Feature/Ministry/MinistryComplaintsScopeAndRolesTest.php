<?php

namespace Tests\Feature\Ministry;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\Department;
use App\Models\Ministry;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MinistryComplaintsScopeAndRolesTest extends TestCase
{
    use RefreshDatabase;

    protected Role $superAdminRole;

    protected Role $ministryAdminRole;

    protected Role $fieldWorkerRole;

    protected Role $citizenRole;

    protected Ministry $ministryA;

    protected Department $departmentA1;

    protected Department $departmentA2;

    protected Category $categoryA1;

    protected Category $categoryA2;

    protected User $ministryAdminA;

    protected Ministry $ministryB;

    protected Department $departmentB1;

    protected Category $categoryB1;

    protected User $citizen;

    protected User $fieldWorker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->superAdminRole = Role::where('name', 'Super Admin')->firstOrFail();
        $this->ministryAdminRole = Role::where('name', 'Ministry Admin')->firstOrFail();
        $this->fieldWorkerRole = Role::where('name', 'Field Worker')->firstOrFail();
        $this->citizenRole = Role::where('name', 'Citizen')->firstOrFail();

        // Ministry A with 2 departments
        $this->ministryA = Ministry::create([
            'name' => 'وزارة الأشغال العامة والطرق',
            'code' => 'MOPW',
            'logo' => 'logos/mopw.png',
            'contact_email' => 'contact@mopw.gov.ye',
            'is_active' => true,
        ]);

        $this->departmentA1 = Department::create([
            'ministry_id' => $this->ministryA->id,
            'name' => 'إدارة الطرق والجسور',
            'description' => 'صيانة الطرق والجسور',
            'is_active' => true,
        ]);

        $this->departmentA2 = Department::create([
            'ministry_id' => $this->ministryA->id,
            'name' => 'إدارة المرافق والمباني الحكومية',
            'description' => 'متابعة سلامة المنشآت الخدمية والمرافق',
            'is_active' => true,
        ]);

        $this->categoryA1 = Category::create([
            'name' => 'حفر وتشققات إسفلتية خطرة',
            'department_id' => $this->departmentA1->id,
            'description' => 'حفر في الطرق',
            'level' => 1,
        ]);

        $this->categoryA2 = Category::create([
            'name' => 'تضرر اللوحات الإرشادية والمرافق',
            'department_id' => $this->departmentA2->id,
            'description' => 'تلف الإشارات واللوحات',
            'level' => 1,
        ]);

        // Ministry Admin assigned specifically to Department A1 (Roads)
        $this->ministryAdminA = User::factory()->create([
            'name' => 'مشرف وزارة الأشغال',
            'email' => 'ministry_admin_a@bader.test',
            'department_id' => $this->departmentA1->id,
            'is_active' => true,
        ]);
        $this->ministryAdminA->roles()->sync([$this->ministryAdminRole->id]);

        // Ministry B with 1 department (Other Ministry)
        $this->ministryB = Ministry::create([
            'name' => 'وزارة الكهرباء والطاقة',
            'code' => 'MOEE',
            'logo' => 'logos/moee.png',
            'contact_email' => 'contact@moee.gov.ye',
            'is_active' => true,
        ]);

        $this->departmentB1 = Department::create([
            'ministry_id' => $this->ministryB->id,
            'name' => 'إدارة صيانة الشبكة الكهربائية',
            'description' => 'صيانة شبكات الكهرباء',
            'is_active' => true,
        ]);

        $this->categoryB1 = Category::create([
            'name' => 'أسلاك كهربائية مكشوفة',
            'department_id' => $this->departmentB1->id,
            'description' => 'مخاطر كابلات الكهرباء',
            'level' => 1,
        ]);

        // Citizen
        $this->citizen = User::factory()->create([
            'name' => 'مواطن تجريبي',
            'email' => 'citizen@bader.test',
            'department_id' => null,
            'is_active' => true,
        ]);
        $this->citizen->roles()->sync([$this->citizenRole->id]);

        // Field Worker in Ministry A
        $this->fieldWorker = User::factory()->create([
            'name' => 'مهندس ميداني',
            'email' => 'worker@bader.test',
            'department_id' => $this->departmentA1->id,
            'is_active' => true,
        ]);
        $this->fieldWorker->roles()->sync([$this->fieldWorkerRole->id]);
    }

    /**
     * Case 1: Ministry admin whose account is in department A1 can see complaints assigned
     * to department A2 (facilities) in dashboard and complaints index.
     */
    public function test_ministry_admin_sees_complaint_assigned_to_other_department_in_same_ministry(): void
    {
        // Complaint #22 scenario: Routed to departmentA2 (المرافق والمباني)
        $complaintInA2 = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->categoryA2->id,
            'current_department_id' => $this->departmentA2->id,
            'title' => 'بلاغ تجريبي رقم 22 لوحات إرشادية',
            'description' => 'وصف البلاغ التجريبي رقم 22 المسند لإدارة المرافق.',
            'status' => 'new',
            'priority' => 'low',
            'latitude' => 15.3500,
            'longitude' => 44.2000,
        ]);

        // 1. Dashboard View
        $dashboardResponse = $this->actingAs($this->ministryAdminA)->get(route('ministry.dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee($complaintInA2->title);
        $dashboardResponse->assertSee($complaintInA2->complaint_number);

        $recentComplaints = $dashboardResponse->viewData('recentComplaints');
        $this->assertTrue($recentComplaints->contains('id', $complaintInA2->id));

        // 2. Complaints Index View
        $indexResponse = $this->actingAs($this->ministryAdminA)->get(route('ministry.complaints.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee($complaintInA2->title);
        $indexResponse->assertSee($complaintInA2->complaint_number);

        $complaints = $indexResponse->viewData('complaints');
        $this->assertTrue($complaints->contains('id', $complaintInA2->id));
    }

    /**
     * Case 2: Complaints belonging to another ministry are completely excluded from ministry admin view.
     */
    public function test_complaints_belonging_to_another_ministry_are_never_visible_to_ministry_admin(): void
    {
        $foreignComplaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->categoryB1->id,
            'current_department_id' => $this->departmentB1->id,
            'title' => 'بلاغ كهرباء تابع لوزارة الكهرباء',
            'description' => 'انقطاع التيار في محول الكهرباء.',
            'status' => 'new',
            'priority' => 'urgent',
            'latitude' => 15.3600,
            'longitude' => 44.2100,
        ]);

        // Dashboard check
        $dashboardResponse = $this->actingAs($this->ministryAdminA)->get(route('ministry.dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertDontSee($foreignComplaint->title);
        $dashboardResponse->assertDontSee($foreignComplaint->complaint_number);

        $recentComplaints = $dashboardResponse->viewData('recentComplaints');
        $this->assertFalse($recentComplaints->contains('id', $foreignComplaint->id));

        // Complaints Index check
        $indexResponse = $this->actingAs($this->ministryAdminA)->get(route('ministry.complaints.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertDontSee($foreignComplaint->title);
        $indexResponse->assertDontSee($foreignComplaint->complaint_number);

        $complaints = $indexResponse->viewData('complaints');
        $this->assertFalse($complaints->contains('id', $foreignComplaint->id));
    }

    /**
     * Case 3: "All departments" option (default) returns complaints across all departments of the ministry.
     */
    public function test_all_departments_filter_returns_complaints_from_all_ministry_departments(): void
    {
        $complaintInA1 = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->categoryA1->id,
            'current_department_id' => $this->departmentA1->id,
            'title' => 'بلاغ صيانة في إدارة الطرق والجسور',
            'description' => 'تشققات أسفلتية خطيرة في الشارع العام.',
            'status' => 'new',
            'priority' => 'medium',
            'latitude' => 15.3510,
            'longitude' => 44.2010,
        ]);

        $complaintInA2 = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->categoryA2->id,
            'current_department_id' => $this->departmentA2->id,
            'title' => 'بلاغ مرافق في إدارة المباني والمرافق',
            'description' => 'لوحة إرشادية متساقطة على الرصيف.',
            'status' => 'new',
            'priority' => 'low',
            'latitude' => 15.3520,
            'longitude' => 44.2020,
        ]);

        // Default request without department_id filter
        $responseDefault = $this->actingAs($this->ministryAdminA)->get(route('ministry.complaints.index'));
        $responseDefault->assertStatus(200);
        $complaintsDefault = $responseDefault->viewData('complaints');
        $this->assertTrue($complaintsDefault->contains('id', $complaintInA1->id));
        $this->assertTrue($complaintsDefault->contains('id', $complaintInA2->id));

        // Explicit request with department_id=all
        $responseAll = $this->actingAs($this->ministryAdminA)->get(route('ministry.complaints.index', ['department_id' => 'all']));
        $responseAll->assertStatus(200);
        $complaintsAll = $responseAll->viewData('complaints');
        $this->assertTrue($complaintsAll->contains('id', $complaintInA1->id));
        $this->assertTrue($complaintsAll->contains('id', $complaintInA2->id));
    }

    /**
     * Case 4: Selecting a specific department restricts results to only that department,
     * and tampering with another ministry's department ID returns 403 Forbidden.
     */
    public function test_selecting_specific_department_scopes_properly_and_rejects_foreign_departments(): void
    {
        $complaintInA1 = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->categoryA1->id,
            'current_department_id' => $this->departmentA1->id,
            'title' => 'بلاغ صيانة الطرق فقط',
            'description' => 'وصف بلاغ الطرق والجسور.',
            'status' => 'new',
            'priority' => 'medium',
            'latitude' => 15.3510,
            'longitude' => 44.2010,
        ]);

        $complaintInA2 = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->categoryA2->id,
            'current_department_id' => $this->departmentA2->id,
            'title' => 'بلاغ صيانة المرافق فقط',
            'description' => 'وصف بلاغ المرافق الحكومية.',
            'status' => 'new',
            'priority' => 'low',
            'latitude' => 15.3520,
            'longitude' => 44.2020,
        ]);

        // 1. Filter by Department A2 specifically
        $responseA2 = $this->actingAs($this->ministryAdminA)->get(route('ministry.complaints.index', [
            'department_id' => $this->departmentA2->id,
        ]));
        $responseA2->assertStatus(200);
        $complaintsA2 = $responseA2->viewData('complaints');
        $this->assertTrue($complaintsA2->contains('id', $complaintInA2->id));
        $this->assertFalse($complaintsA2->contains('id', $complaintInA1->id));

        // 2. Tampering test: Passing departmentB1 (which belongs to Ministry B)
        $tamperedResponse = $this->actingAs($this->ministryAdminA)->get(route('ministry.complaints.index', [
            'department_id' => $this->departmentB1->id,
        ]));
        $tamperedResponse->assertStatus(403);
    }

    /**
     * Case 5: Complaint details dossier respects ministry boundary strictly.
     */
    public function test_complaint_details_dossier_strictly_confined_to_ministry(): void
    {
        $complaintInA2 = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->categoryA2->id,
            'current_department_id' => $this->departmentA2->id,
            'title' => 'بلاغ تفصيلي في المرافق',
            'description' => 'فحص إمكانية فتح تفاصيل البلاغ من قبل مشرف الوزارة.',
            'status' => 'new',
            'priority' => 'high',
            'latitude' => 15.3500,
            'longitude' => 44.2000,
        ]);

        $foreignComplaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->categoryB1->id,
            'current_department_id' => $this->departmentB1->id,
            'title' => 'بلاغ كهرباء أجنبي',
            'description' => 'لا يجوز لمشرف الأشغال رؤيته.',
            'status' => 'new',
            'priority' => 'urgent',
            'latitude' => 15.3600,
            'longitude' => 44.2100,
        ]);

        // Ministry Admin can view complaint from other department within same ministry
        $allowedResponse = $this->actingAs($this->ministryAdminA)->get(route('ministry.complaints.show', $complaintInA2->id));
        $allowedResponse->assertStatus(200);

        // Ministry Admin CANNOT view complaint from another ministry
        $deniedResponse = $this->actingAs($this->ministryAdminA)->get(route('ministry.complaints.show', $foreignComplaint->id));
        $deniedResponse->assertStatus(403);
    }

    /**
     * Case 6: Non-administrative accounts (Citizen, Field Worker) cannot access web admin dashboards.
     */
    public function test_non_admin_accounts_cannot_access_super_admin_or_ministry_dashboards(): void
    {
        // Citizen
        $this->actingAs($this->citizen)->get(route('admin.dashboard'))->assertStatus(403);
        $this->actingAs($this->citizen)->get(route('ministry.dashboard'))->assertStatus(403);
        $this->actingAs($this->citizen)->get(route('ministry.complaints.index'))->assertStatus(403);

        // Field Worker
        $this->actingAs($this->fieldWorker)->get(route('admin.dashboard'))->assertStatus(403);
        $this->actingAs($this->fieldWorker)->get(route('ministry.dashboard'))->assertStatus(403);
        $this->actingAs($this->fieldWorker)->get(route('ministry.complaints.index'))->assertStatus(403);
    }

    /**
     * Case 7: Worker and Citizen maintain their mobile API authorized functionality.
     */
    public function test_worker_and_citizen_maintain_mobile_api_functionality(): void
    {
        // 1. Citizen can query public/own complaints via API
        Sanctum::actingAs($this->citizen);
        $citizenApiResp = $this->getJson('/api/v1/complaints');
        $citizenApiResp->assertStatus(200);

        // 2. Field worker can access assignments endpoint via API
        Sanctum::actingAs($this->fieldWorker);
        $workerApiResp = $this->getJson('/api/v1/field-assignments');
        $workerApiResp->assertStatus(200);
    }

    /**
     * Case 8: Complaint status counters and filters handle both 'new' and 'submitted' seamlessly.
     */
    public function test_status_counters_and_filters_support_new_and_submitted_consistently(): void
    {
        $complaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->categoryA1->id,
            'current_department_id' => $this->departmentA1->id,
            'title' => 'بلاغ بحالة جديد وارد',
            'description' => 'فحص فلترة الحالة بعد التحديث.',
            'status' => 'new',
            'priority' => 'low',
            'latitude' => 15.3500,
            'longitude' => 44.2000,
        ]);

        // Filter by status=new
        $responseNew = $this->actingAs($this->ministryAdminA)->get(route('ministry.complaints.index', ['status' => 'new']));
        $responseNew->assertStatus(200);
        $complaintsNew = $responseNew->viewData('complaints');
        $this->assertTrue($complaintsNew->contains('id', $complaint->id));

        // Filter by status=submitted
        $responseSubmitted = $this->actingAs($this->ministryAdminA)->get(route('ministry.complaints.index', ['status' => 'submitted']));
        $responseSubmitted->assertStatus(200);
        $complaintsSubmitted = $responseSubmitted->viewData('complaints');
        $this->assertTrue($complaintsSubmitted->contains('id', $complaint->id));

        // Overview count in DashboardController
        $dashboardResponse = $this->actingAs($this->ministryAdminA)->get(route('ministry.dashboard'));
        $dashboardResponse->assertStatus(200);
        $overview = $dashboardResponse->viewData('deptOverview');
        $this->assertGreaterThanOrEqual(1, $overview['submitted']);
    }

    /**
     * Case 9: Accounts where ministry scope cannot be reliably resolved fail securely (403),
     * rather than getting unrestricted access.
     */
    public function test_accounts_with_unresolvable_ministry_fail_securely_with_403(): void
    {
        // 1. Ministry admin with null department_id cannot access dashboard or complaints
        $unboundAdmin = User::factory()->create([
            'department_id' => null,
            'is_active' => true,
        ]);
        $unboundAdmin->roles()->sync([$this->ministryAdminRole->id]);

        $this->actingAs($unboundAdmin)->get(route('ministry.dashboard'))->assertStatus(403);
        $this->actingAs($unboundAdmin)->get(route('ministry.complaints.index'))->assertStatus(403);
    }
}
