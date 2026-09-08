<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\Department;
use App\Models\FieldAssignment;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\MinistryDepartmentSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FieldAndTransferApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $citizen;
    protected User $ministryAdmin;
    protected User $fieldWorker;
    protected Category $category;
    protected Department $department1;
    protected Department $department2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(MinistryDepartmentSeeder::class);
        $this->seed(CategorySeeder::class);

        $this->department1 = Department::first();
        $this->department2 = Department::skip(1)->first();

        // Citizen
        $this->citizen = User::factory()->create(['is_active' => true]);
        $this->citizen->roles()->attach(Role::where('name', 'Citizen')->first()->id);

        // Ministry Admin for department1's ministry
        $this->ministryAdmin = User::factory()->create([
            'department_id' => $this->department1->id,
            'is_active' => true,
        ]);
        $this->ministryAdmin->roles()->attach(Role::where('name', 'Ministry Admin')->first()->id);

        // Field Worker
        $this->fieldWorker = User::factory()->create([
            'department_id' => $this->department1->id,
            'is_active' => true,
        ]);
        $this->fieldWorker->roles()->attach(Role::where('name', 'Field Worker')->first()->id);

        $this->category = Category::where('department_id', $this->department1->id)->first()
            ?? Category::whereNotNull('department_id')->first();
    }

    public function test_admin_can_transfer_complaint_to_another_department(): void
    {
        $complaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->department1->id,
            'title' => 'بلاغ يحتاج إلى اختصاص إدارة أخرى',
            'description' => 'المشكلة تقع ضمن نطاق شبكات الصرف الصحي وليس الطرق.',
            'status' => 'new',
            'priority' => 'medium',
            'latitude' => 15.350000,
            'longitude' => 44.200000,
        ]);

        $response = $this->actingAs($this->ministryAdmin, 'sanctum')
            ->postJson("/api/v1/complaints/{$complaint->id}/transfer", [
                'to_department_id' => $this->department2->id,
                'reason' => 'عدم الاختصاص وإحالة الموضوع للجهة المعنية فنياً.',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'current_department_id' => $this->department2->id,
            'status' => 'under_review',
        ]);

        $this->assertDatabaseHas('complaint_transfers', [
            'complaint_id' => $complaint->id,
            'from_department_id' => $this->department1->id,
            'to_department_id' => $this->department2->id,
            'transferred_by' => $this->ministryAdmin->id,
        ]);
    }

    public function test_field_worker_assignment_lifecycle_with_geofencing(): void
    {
        // 1. Complaint created at (15.350000, 44.200000)
        $complaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->department1->id,
            'title' => 'معاينة تضرر الطريق',
            'description' => 'هبوط إسفلتي خطير يحتاج لمعاينة وتحديد حجم الضرر.',
            'status' => 'under_review',
            'priority' => 'high',
            'latitude' => 15.350000,
            'longitude' => 44.200000,
        ]);

        // 2. Admin assigns field worker
        $assignResponse = $this->actingAs($this->ministryAdmin, 'sanctum')
            ->postJson("/api/v1/complaints/{$complaint->id}/assign", [
                'user_id' => $this->fieldWorker->id,
                'notes' => 'يرجى الانتقال للموقع وتوثيق المشكلة بالصور.',
            ]);

        $assignResponse->assertStatus(201)
            ->assertJsonPath('success', true);

        $assignment = FieldAssignment::where('complaint_id', $complaint->id)->first();
        $this->assertNotNull($assignment);
        $this->assertEquals('pending', $assignment->status);
        $this->assertEquals('assigned', $complaint->fresh()->status);

        // 3. Worker starts assignment
        $startResponse = $this->actingAs($this->fieldWorker, 'sanctum')
            ->postJson("/api/v1/field-assignments/{$assignment->id}/start");

        $startResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertEquals('in_progress', $assignment->fresh()->status);
        $this->assertEquals('in_progress', $complaint->fresh()->status);

        // 4. Worker attempts to complete from a location far away (e.g. 5km away)
        $farResponse = $this->actingAs($this->fieldWorker, 'sanctum')
            ->postJson("/api/v1/field-assignments/{$assignment->id}/complete", [
                'report' => 'تم ردم الحفرة وإعادة فتح الطريق أمام السيارات.',
                'latitude' => 15.400000, // ~5.5 km away
                'longitude' => 44.250000,
            ]);

        $farResponse->assertStatus(422)
            ->assertJsonPath('success', false);

        // 5. Worker completes from close proximity (< 100 meters)
        $successResponse = $this->actingAs($this->fieldWorker, 'sanctum')
            ->postJson("/api/v1/field-assignments/{$assignment->id}/complete", [
                'report' => 'تم التواجد بالموقع وردم الهبوط الإسفلتي وإصلاح الضرر بالكامل.',
                'latitude' => 15.350100, // ~15 meters away
                'longitude' => 44.200050,
            ]);

        $successResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertEquals('completed', $assignment->fresh()->status);
        $this->assertEquals('resolved', $complaint->fresh()->status);

        $this->assertDatabaseHas('complaint_timeline', [
            'complaint_id' => $complaint->id,
            'event_type' => 'resolved',
            'new_value' => 'resolved',
        ]);
    }
}
