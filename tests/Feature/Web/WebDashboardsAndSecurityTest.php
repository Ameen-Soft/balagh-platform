<?php

namespace Tests\Feature\Web;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\ComplaintAttachment;
use App\Models\ComplaintTimeline;
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

class WebDashboardsAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $ministryAdmin;
    protected User $citizen;
    protected Ministry $ministry;
    protected Department $department;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        // 1. Structure
        $this->ministry = Ministry::create([
            'name' => 'وزارة الأشغال العامة والطرق',
            'code' => 'MPW',
            'logo' => 'logos/mpw.png',
            'contact_email' => 'contact@mpw.gov.ye',
            'is_active' => true,
        ]);

        $this->department = Department::create([
            'ministry_id' => $this->ministry->id,
            'name' => 'إدارة صيانة الطرق',
            'description' => 'صيانة شبكات الطرق والجسور العامة',
            'is_active' => true,
        ]);

        $this->category = Category::create([
            'department_id' => $this->department->id,
            'name' => 'هبوط إسفلتي وحفر خطيرة',
            'description' => 'هبوط وحفر وتشققات في طبقة الأسفلت تتطلب إصلاحاً سريعاً',
            'level' => 1,
        ]);

        // 2. Roles
        $superAdminRole = Role::where('name', 'Super Admin')->firstOrFail();
        $ministryAdminRole = Role::where('name', 'Ministry Admin')->firstOrFail();
        $citizenRole = Role::where('name', 'Citizen')->firstOrFail();

        // 3. Users
        $this->superAdmin = User::factory()->create([
            'name' => 'المدير العام للنظام',
            'email' => 'superadmin@balagh.gov.ye',
            'department_id' => null,
            'is_active' => true,
        ]);
        $this->superAdmin->roles()->sync([$superAdminRole->id]);

        $this->ministryAdmin = User::factory()->create([
            'name' => 'مشرف وزارة الأشغال',
            'email' => 'ministryadmin@balagh.gov.ye',
            'department_id' => $this->department->id,
            'is_active' => true,
        ]);
        $this->ministryAdmin->roles()->sync([$ministryAdminRole->id]);

        $this->citizen = User::factory()->create([
            'name' => 'أحمد المواطن',
            'email' => 'citizen@balagh.gov.ye',
            'phone' => '777000111',
            'national_id' => '1000000001',
            'department_id' => null,
            'is_active' => true,
        ]);
        $this->citizen->roles()->sync([$citizenRole->id]);
    }

    public function test_public_landing_page_loads_successfully_with_national_identity(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertSee('بَــــادِر')
            ->assertSee('الجمهورية اليمنية')
            ->assertSee('إجمالي البلاغات المسجلة')
            ->assertSee('نسبة الإنجاز والمعالجة');
    }

    public function test_public_transparency_complaints_list_and_detail_masks_citizen_privacy(): void
    {
        $complaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->department->id,
            'title' => 'حفرة عميقة في شارع الستين',
            'description' => 'تسببت بأضرار في المركبات ونطلب التدخل السريع من إدارة الطرق.',
            'status' => 'resolved',
            'priority' => 'high',
            'latitude' => 15.3500,
            'longitude' => 44.2000,
            'governorate' => 'صنعاء',
        ]);

        // 1. Index
        $responseIndex = $this->get(route('public.complaints.index'));
        $responseIndex->assertStatus(200)
            ->assertSee('حفرة عميقة في شارع الستين')
            ->assertSee('#' . $complaint->complaint_number);

        // 2. Show
        $responseShow = $this->get(route('public.complaints.show', $complaint->id));
        $responseShow->assertStatus(200)
            ->assertSee('حفرة عميقة في شارع الستين')
            ->assertSee('تم حجب البيانات الشخصية للمواطن')
            // Sensitive phone and national id of citizen must NOT appear in public transparency
            ->assertDontSee($this->citizen->phone)
            ->assertDontSee($this->citizen->national_id);
    }

    public function test_public_projects_list_and_details_load_properly(): void
    {
        $project = Project::create([
            'created_by' => $this->superAdmin->id,
            'department_id' => $this->department->id,
            'title' => 'مشروع إعادة تأهيل جسر مذبح',
            'description' => 'مشروع بنية تحتية لتحسين الحركة المرورية',
            'target_amount' => 50000000,
            'current_amount' => 22500000,
            'latitude' => 15.3700,
            'longitude' => 44.1800,
            'status' => 'active',
        ]);

        $response = $this->get(route('public.projects.index'));
        $response->assertStatus(200)
            ->assertSee('مشروع إعادة تأهيل جسر مذبح')
            ->assertSee('45%');

        $responseShow = $this->get(route('public.projects.show', $project->id));
        $responseShow->assertStatus(200)
            ->assertSee('مشروع إعادة تأهيل جسر مذبح')
            ->assertSee('50,000,000');
    }

    public function test_guest_is_redirected_to_login_when_accessing_admin_or_ministry_dashboards(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect('/login');
        $this->get(route('ministry.dashboard'))->assertRedirect('/login');
    }

    public function test_citizen_attempting_to_access_admin_dashboard_gets_403_forbidden(): void
    {
        $response = $this->actingAs($this->citizen)->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    public function test_citizen_attempting_to_access_ministry_dashboard_gets_403_forbidden(): void
    {
        $response = $this->actingAs($this->citizen)->get(route('ministry.dashboard'));

        $response->assertStatus(403);
    }

    public function test_ministry_admin_attempting_to_access_admin_dashboard_gets_403_forbidden(): void
    {
        $response = $this->actingAs($this->ministryAdmin)->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    public function test_super_admin_can_access_admin_dashboard_and_monitoring(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.dashboard'));

        $response->assertStatus(200)
            ->assertSee('لوحة المؤشرات والرقابة الشاملة')
            ->assertSee('إجمالي البلاغات الوطنية');

        $complaintsResponse = $this->actingAs($this->superAdmin)->get(route('admin.complaints.index'));
        $complaintsResponse->assertStatus(200)
            ->assertSee('منظومة الرقابة المركزية للبلاغات والشكاوى');
    }

    public function test_ministry_admin_can_access_ministry_dashboard_and_operational_complaints(): void
    {
        $response = $this->actingAs($this->ministryAdmin)->get(route('ministry.dashboard'));

        $response->assertStatus(200)
            ->assertSee('مؤشرات الأداء والمعالجة التشغيلية')
            ->assertSee($this->department->name);

        $complaintsResponse = $this->actingAs($this->ministryAdmin)->get(route('ministry.complaints.index'));
        $complaintsResponse->assertStatus(200)
            ->assertSee('سجل البلاغات والشكاوى الواردة للإدارة');
    }

    public function test_ministry_admin_can_update_complaint_status_operationally(): void
    {
        $complaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->department->id,
            'title' => 'بلاغ صيانة قاطع إنارة',
            'description' => 'قاطع الإنارة متعطل في الحي السكني.',
            'status' => 'under_review',
            'priority' => 'medium',
            'latitude' => 15.3500,
            'longitude' => 44.2000,
        ]);

        $response = $this->actingAs($this->ministryAdmin)->post(
            route('ministry.complaints.update_status', $complaint->id),
            [
                'status' => 'in_progress',
                'notes' => 'تم استلام البلاغ وجاري توجيه فرقة الصيانة الميدانية.',
            ]
        );

        $response->assertRedirect();
        $this->assertEquals('in_progress', $complaint->fresh()->status);
    }

    public function test_super_admin_cannot_perform_operational_action_on_ministry_complaint(): void
    {
        $complaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->department->id,
            'title' => 'شكوى تابعة لوزارة الأشغال',
            'description' => 'فحص ضوابط الصلاحيات التشغيلية.',
            'status' => 'under_review',
            'priority' => 'medium',
            'latitude' => 15.3500,
            'longitude' => 44.2000,
        ]);

        // ComplaintPolicy::update() requires ministry admin of the complaint's ministry.
        // Super Admin has monitoring bypass ONLY on viewAny and view, not on update / assignFieldWorker / transfer.
        $this->assertTrue($this->superAdmin->can('view', $complaint));
        $this->assertFalse($this->superAdmin->can('update', $complaint));
        $this->assertFalse($this->superAdmin->can('assignFieldWorker', $complaint));
        $this->assertFalse($this->superAdmin->can('transfer', $complaint));
    }

    public function test_login_as_super_admin_redirects_directly_to_admin_dashboard_even_if_stale_intended_url_exists(): void
    {
        // Simulate stale intended URL
        session(['url.intended' => route('ministry.dashboard')]);

        $response = $this->post('/login', [
            'email' => $this->superAdmin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_login_as_ministry_admin_redirects_to_ministry_dashboard(): void
    {
        $response = $this->post('/login', [
            'email' => $this->ministryAdmin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('ministry.dashboard'));
    }

    public function test_ministry_admin_can_view_field_assignments_page(): void
    {
        $fieldWorkerRole = Role::where('name', 'Field Worker')->firstOrFail();
        $worker = User::factory()->create([
            'department_id' => $this->department->id,
            'is_active' => true,
        ]);
        $worker->roles()->sync([$fieldWorkerRole->id]);

        $complaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->department->id,
            'title' => 'معاينة هبوط إسفلتي',
            'description' => 'وصف البلاغ للمعاينة الميدانية.',
            'status' => 'in_progress',
            'priority' => 'high',
            'latitude' => 15.3500,
            'longitude' => 44.2000,
        ]);

        FieldAssignment::create([
            'complaint_id' => $complaint->id,
            'worker_id' => $worker->id,
            'assigned_by' => $this->ministryAdmin->id,
            'status' => 'completed',
            'started_at' => now()->subDay(),
            'completed_at' => now(),
            'notes' => 'تم استكمال المعاينة والردم.',
        ]);

        ComplaintAttachment::create([
            'complaint_id' => $complaint->id,
            'file_path' => 'complaints/evidence_after.jpg',
            'file_type' => 'image/jpeg',
            'captured_latitude' => 15.3500,
            'captured_longitude' => 44.2000,
            'uploaded_by' => $worker->id,
            'type' => 'after',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->ministryAdmin)->get(route('ministry.assignments.index'));

        $response->assertStatus(200)
            ->assertSee('التكليفات والمهام الميدانية')
            ->assertSee($worker->name)
            ->assertSee('1 دليل / صورة');
    }

    public function test_ministry_admin_can_view_transfers_page(): void
    {
        $otherDept = Department::create([
            'ministry_id' => $this->ministry->id,
            'name' => 'إدارة شبكات المياه',
            'description' => 'صيانة شبكات المياه',
            'is_active' => true,
        ]);

        $complaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->department->id,
            'title' => 'تسرب مياه في شارع الزبيري',
            'description' => 'كسر ماسورة مياه رئيسية.',
            'status' => 'under_review',
            'priority' => 'urgent',
            'latitude' => 15.3500,
            'longitude' => 44.2000,
        ]);

        ComplaintTransfer::create([
            'complaint_id' => $complaint->id,
            'from_department_id' => $otherDept->id,
            'to_department_id' => $this->department->id,
            'reason' => 'عدم الاختصاص وإحالة للأشغال للطرق المتضررة',
            'transferred_by' => $this->ministryAdmin->id,
        ]);

        $response = $this->actingAs($this->ministryAdmin)->get(route('ministry.transfers.index', ['type' => 'incoming']));

        $response->assertStatus(200)
            ->assertSee('سجل الإحالات بين الجهات الحكومية')
            ->assertSee('عدم الاختصاص وإحالة للأشغال للطرق المتضررة')
            ->assertSee($this->ministryAdmin->name);
    }

    public function test_ministry_admin_and_super_admin_can_view_complaint_dossier_with_timelines_and_evidence(): void
    {
        $complaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->department->id,
            'title' => 'بلاغ متكامل مع سجل تاريخي وتكليف',
            'description' => 'فحص شمولية استعراض ملف البلاغ الكامل.',
            'status' => 'resolved',
            'priority' => 'high',
            'latitude' => 15.3500,
            'longitude' => 44.2000,
        ]);

        ComplaintTimeline::create([
            'complaint_id' => $complaint->id,
            'event_type' => 'created',
            'description' => 'تم إنشاء البلاغ من تطبيق المواطن.',
            'performed_by' => $this->citizen->id,
            'created_at' => now()->subHours(2),
        ]);

        ComplaintTimeline::create([
            'complaint_id' => $complaint->id,
            'event_type' => 'resolved',
            'description' => 'تم حل البلاغ واستكمال الإجراءات.',
            'performed_by' => $this->ministryAdmin->id,
            'created_at' => now()->subHour(),
        ]);

        // 1. Ministry Admin view
        $ministryResponse = $this->actingAs($this->ministryAdmin)->get(route('ministry.complaints.show', $complaint->id));
        $ministryResponse->assertStatus(200)
            ->assertSee('معالجة البلاغ')
            ->assertSee($this->citizen->name)
            ->assertSee('إنشاء البلاغ')
            ->assertSee('معالجة البلاغ')
            ->assertSee($this->ministryAdmin->name);

        // 2. Super Admin view (Audit dossier)
        $adminResponse = $this->actingAs($this->superAdmin)->get(route('admin.complaints.show', $complaint->id));
        $adminResponse->assertStatus(200)
            ->assertSee('ملف الرقابة الشامل للبلاغ')
            ->assertSee($this->citizen->name)
            ->assertSee('إنشاء البلاغ')
            ->assertSee('معالجة البلاغ');
    }

    public function test_super_admin_can_view_all_management_modules(): void
    {
        // 1. Ministries & Departments
        $this->actingAs($this->superAdmin)
            ->get(route('admin.ministries.index'))
            ->assertStatus(200)
            ->assertSee('إدارة الهيكل المؤسسي والوزارات')
            ->assertSee($this->ministry->name);

        // 2. Categories
        $this->actingAs($this->superAdmin)
            ->get(route('admin.categories.index'))
            ->assertStatus(200)
            ->assertSee('إدارة تصنيفات البلاغات')
            ->assertSee($this->category->name);

        // 3. Users Management
        $this->actingAs($this->superAdmin)
            ->get(route('admin.users.index'))
            ->assertStatus(200)
            ->assertSee('إدارة المستخدمين والأدوار والكوادر الحكومية')
            ->assertSee($this->superAdmin->name);

        // 4. Projects Management
        $this->actingAs($this->superAdmin)
            ->get(route('admin.projects.index'))
            ->assertStatus(200)
            ->assertSee('إدارة المشاريع التنموية الوطنية والرقابة');
    }
}
