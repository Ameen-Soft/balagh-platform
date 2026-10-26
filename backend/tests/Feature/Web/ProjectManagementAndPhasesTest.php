<?php

namespace Tests\Feature\Web;

use App\Models\Department;
use App\Models\Ministry;
use App\Models\Project;
use App\Models\ProjectPhase;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectManagementAndPhasesTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $ministryAdmin;

    protected User $otherMinistryAdmin;

    protected Ministry $ministry;

    protected Department $department;

    protected Ministry $otherMinistry;

    protected Department $otherDepartment;

    protected Project $project;

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

        $this->otherMinistry = Ministry::create([
            'name' => 'وزارة المياه والبيئة',
            'code' => 'MWE',
            'logo' => 'logos/mwe.png',
            'contact_email' => 'contact@mwe.gov.ye',
            'is_active' => true,
        ]);

        $this->otherDepartment = Department::create([
            'ministry_id' => $this->otherMinistry->id,
            'name' => 'إدارة الصرف الصحي',
            'description' => 'صيانة شبكات الصرف الصحي والمياه',
            'is_active' => true,
        ]);

        // 2. Roles & Users
        $superAdminRole = Role::where('name', 'Super Admin')->firstOrFail();
        $ministryAdminRole = Role::where('name', 'Ministry Admin')->firstOrFail();

        $this->superAdmin = User::factory()->create([
            'name' => 'Super Admin User',
            'email' => 'superadmin@balagh.gov.ye',
            'department_id' => null,
            'is_active' => true,
        ]);
        $this->superAdmin->roles()->sync([$superAdminRole->id]);

        $this->ministryAdmin = User::factory()->create([
            'name' => 'Ministry Admin User',
            'email' => 'ministryadmin@mpw.gov.ye',
            'department_id' => $this->department->id,
            'is_active' => true,
        ]);
        $this->ministryAdmin->roles()->sync([$ministryAdminRole->id]);

        $this->otherMinistryAdmin = User::factory()->create([
            'name' => 'Other Ministry Admin',
            'email' => 'otheradmin@mwe.gov.ye',
            'department_id' => $this->otherDepartment->id,
            'is_active' => true,
        ]);
        $this->otherMinistryAdmin->roles()->sync([$ministryAdminRole->id]);

        // 3. Project
        $this->project = Project::create([
            'title' => 'مشروع تأهيل طريق التحرير',
            'description' => 'إعادة تأهيل وسفلتة المسارات الرئيسية',
            'department_id' => $this->department->id,
            'target_amount' => 500000,
            'current_amount' => 150000,
            'status' => 'in_progress',
            'latitude' => 15.3547,
            'longitude' => 44.2066,
            'created_by' => $this->ministryAdmin->id,
        ]);
    }

    public function test_super_admin_can_update_project_details(): void
    {
        $response = $this->actingAs($this->superAdmin)->put(route('admin.projects.update', $this->project), [
            'name' => 'مشروع تأهيل طريق التحرير المعدل',
            'department_id' => $this->otherDepartment->id,
            'budget' => 750000,
            'current_amount' => 200000,
            'status' => 'active',
            'latitude' => 15.3700,
            'longitude' => 44.1950,
            'description' => 'وصف تنموي محدث وشامل',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('projects', [
            'id' => $this->project->id,
            'title' => 'مشروع تأهيل طريق التحرير المعدل',
            'department_id' => $this->otherDepartment->id,
            'target_amount' => 750000,
            'status' => 'active',
        ]);
    }

    public function test_super_admin_can_delete_project(): void
    {
        $response = $this->actingAs($this->superAdmin)->delete(route('admin.projects.destroy', $this->project));

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('projects', ['id' => $this->project->id]);
    }

    public function test_super_admin_can_manage_project_phases_full_crud(): void
    {
        // 1. Create Phase
        $createResponse = $this->actingAs($this->superAdmin)->post(route('admin.projects.phases.store', $this->project), [
            'name' => 'المرحلة 1: كشط الأسفلت التالف',
            'description' => 'إزالة الطبقة السطحية بطول 2 كم',
            'completion_percentage' => 40,
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-15',
            'status' => 'in_progress',
        ]);

        $createResponse->assertSessionHas('success');
        $phase = ProjectPhase::where('project_id', $this->project->id)->firstOrFail();
        $this->assertEquals('المرحلة 1: كشط الأسفلت التالف', $phase->name);
        $this->assertEquals(40, $phase->completion_percentage);

        // 2. Update Phase
        $updateResponse = $this->actingAs($this->superAdmin)->put(route('admin.projects.phases.update', [$this->project, $phase]), [
            'name' => 'المرحلة 1: اكتمال كشط الأسفلت',
            'description' => 'تم إنهاء كافة أعمال الكشط',
            'completion_percentage' => 100,
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-15',
            'status' => 'completed',
        ]);

        $updateResponse->assertSessionHas('success');
        $phase->refresh();
        $this->assertEquals('المرحلة 1: اكتمال كشط الأسفلت', $phase->name);
        $this->assertEquals(100, $phase->completion_percentage);
        $this->assertEquals('completed', $phase->status);

        // 3. Delete Phase
        $deleteResponse = $this->actingAs($this->superAdmin)->delete(route('admin.projects.phases.destroy', [$this->project, $phase]));
        $deleteResponse->assertSessionHas('success');
        $this->assertDatabaseMissing('project_phases', ['id' => $phase->id]);
    }

    public function test_ministry_admin_can_update_own_ministry_project(): void
    {
        $response = $this->actingAs($this->ministryAdmin)->put(route('ministry.projects.update', $this->project), [
            'name' => 'مشروع الوزارة بعد التعديل',
            'department_id' => $this->department->id,
            'budget' => 600000,
            'current_amount' => 180000,
            'status' => 'in_progress',
            'latitude' => 15.3547,
            'longitude' => 44.2066,
            'description' => 'وصف جديد ومعدل بواسطة مدير الوزارة',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('projects', [
            'id' => $this->project->id,
            'title' => 'مشروع الوزارة بعد التعديل',
            'target_amount' => 600000,
        ]);
    }

    public function test_ministry_admin_cannot_update_other_ministry_project(): void
    {
        $response = $this->actingAs($this->otherMinistryAdmin)->put(route('ministry.projects.update', $this->project), [
            'name' => 'تعديل غير مصرح به',
            'department_id' => $this->otherDepartment->id,
            'budget' => 999999,
            'latitude' => 15.3547,
            'longitude' => 44.2066,
            'description' => 'محاولة اختراق الصلاحيات',
            'status' => 'in_progress',
        ]);

        $response->assertStatus(403);
    }

    public function test_ministry_admin_can_manage_phases_for_own_project(): void
    {
        // 1. Create Phase
        $createResponse = $this->actingAs($this->ministryAdmin)->post(route('ministry.projects.phases.store', $this->project), [
            'name' => 'مرحلة فحص التربة والأساسات',
            'description' => 'فحوصات جيولوجية وهندسية',
            'completion_percentage' => 80,
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-20',
            'status' => 'in_progress',
        ]);

        $createResponse->assertSessionHas('success');
        $phase = ProjectPhase::where('project_id', $this->project->id)->firstOrFail();
        $this->assertEquals(80, $phase->completion_percentage);

        // 2. Update Phase
        $updateResponse = $this->actingAs($this->ministryAdmin)->put(route('ministry.projects.phases.update', [$this->project, $phase]), [
            'name' => 'مرحلة فحص التربة والأساسات - مكتملة',
            'description' => 'تم استلام تقرير الفحص المخبري',
            'completion_percentage' => 100,
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-20',
            'status' => 'completed',
        ]);

        $updateResponse->assertSessionHas('success');
        $this->assertDatabaseHas('project_phases', [
            'id' => $phase->id,
            'name' => 'مرحلة فحص التربة والأساسات - مكتملة',
            'status' => 'completed',
        ]);

        // 3. Delete Phase
        $deleteResponse = $this->actingAs($this->ministryAdmin)->delete(route('ministry.projects.phases.destroy', [$this->project, $phase]));
        $deleteResponse->assertSessionHas('success');
        $this->assertDatabaseMissing('project_phases', ['id' => $phase->id]);
    }

    public function test_ministry_admin_cannot_manage_phases_for_other_ministry_project(): void
    {
        $response = $this->actingAs($this->otherMinistryAdmin)->post(route('ministry.projects.phases.store', $this->project), [
            'name' => 'مرحلة محظورة',
            'completion_percentage' => 10,
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-20',
            'status' => 'pending',
        ]);

        $response->assertStatus(403);
    }
}
