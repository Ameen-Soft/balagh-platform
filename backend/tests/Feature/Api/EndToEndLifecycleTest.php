<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\Department;
use App\Models\FieldAssignment;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\MinistryDepartmentSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EndToEndLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(MinistryDepartmentSeeder::class);
        $this->seed(CategorySeeder::class);
    }

    public function test_complete_end_to_end_citizen_to_field_resolution_lifecycle(): void
    {
        // -------------------------------------------------------------
        // STEP 1: Citizen Registration & Token Issuance
        // -------------------------------------------------------------
        $citizenRegisterResponse = $this->postJson('/api/v1/auth/register', [
            'name' => 'ياسر العولقي',
            'email' => 'yasser.citizen@balagh.gov.ye',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'phone' => '771234567',
            'national_id' => '1020304050',
        ]);

        $citizenRegisterResponse->assertStatus(201);
        $citizenToken = $citizenRegisterResponse->json('data.token');
        $citizenId = $citizenRegisterResponse->json('data.user.id');
        $this->assertNotEmpty($citizenToken);

        // -------------------------------------------------------------
        // STEP 2: Citizen Files a New Complaint
        // -------------------------------------------------------------
        $category = Category::whereNotNull('department_id')->first();
        $incidentLat = 15.369445;
        $incidentLon = 44.191007;

        $createComplaintResponse = $this->withHeader('Authorization', "Bearer {$citizenToken}")
            ->postJson('/api/v1/complaints', [
                'title' => 'ماس كهربائي في كابينة توزيع الحي',
                'description' => 'كابينة الكهرباء الرئيسية مفتوحة وتصدر شرراً كهربائياً يشكل خطراً كبيراً.',
                'category_id' => $category->id,
                'latitude' => $incidentLat,
                'longitude' => $incidentLon,
                'priority' => 'urgent',
            ]);

        $createComplaintResponse->assertStatus(201);
        $complaintId = $createComplaintResponse->json('data.id');
        $this->assertEquals('new', $createComplaintResponse->json('data.status'));
        $this->assertEquals($category->department_id, $createComplaintResponse->json('data.department.id'));

        // Verify Database Records
        $this->assertDatabaseHas('complaints', [
            'id' => $complaintId,
            'status' => 'new',
            'citizen_id' => $citizenId,
        ]);

        $this->assertDatabaseHas('complaint_timeline', [
            'complaint_id' => $complaintId,
            'event_type' => 'created',
        ]);

        // -------------------------------------------------------------
        // STEP 3: Ministry Admin Reviews & Assigns Field Worker
        // -------------------------------------------------------------
        $admin = User::factory()->create([
            'department_id' => $category->department_id,
            'is_active' => true,
        ]);
        $admin->roles()->attach(Role::where('name', 'Ministry Admin')->first()->id);
        $adminToken = $admin->createToken('admin_token')->plainTextToken;

        $worker = User::factory()->create([
            'department_id' => $category->department_id,
            'is_active' => true,
        ]);
        $worker->roles()->attach(Role::where('name', 'Field Worker')->first()->id);
        $workerToken = $worker->createToken('worker_token')->plainTextToken;

        // Admin assigns worker
        $assignResponse = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/complaints/{$complaintId}/assign", [
                'user_id' => $worker->id,
                'notes' => 'يرجى الانتقال الفوري للموقع وتأمين كابينة الكهرباء وإصلاح الخلل.',
            ]);

        $assignResponse->assertStatus(201);
        $assignmentId = $assignResponse->json('data.id');
        $this->assertEquals('pending', $assignResponse->json('data.status'));

        $this->assertDatabaseHas('complaints', [
            'id' => $complaintId,
            'status' => 'assigned',
        ]);

        // -------------------------------------------------------------
        // STEP 4: Field Worker Starts the Task On-Site
        // -------------------------------------------------------------
        $startResponse = $this->actingAs($worker, 'sanctum')
            ->postJson("/api/v1/field-assignments/{$assignmentId}/start");

        $startResponse->assertStatus(200);
        $this->assertEquals('in_progress', $startResponse->json('data.status'));

        $this->assertDatabaseHas('complaints', [
            'id' => $complaintId,
            'status' => 'in_progress',
        ]);

        // -------------------------------------------------------------
        // STEP 5: Field Worker Completes the Task with Valid Geolocation
        // -------------------------------------------------------------
        $completeResponse = $this->actingAs($worker, 'sanctum')
            ->postJson("/api/v1/field-assignments/{$assignmentId}/complete", [
                'report' => 'تم الانتقال للموقع وإصلاح العطل الكهربائي وإحكام قفل الكابينة بنجاح وتأمين المارة.',
                'latitude' => 15.369460, // 2 meters away from incident site
                'longitude' => 44.191015,
            ]);

        $completeResponse->assertStatus(200);
        $this->assertEquals('completed', $completeResponse->json('data.status'));

        // Complaint state must be resolved now
        $this->assertDatabaseHas('complaints', [
            'id' => $complaintId,
            'status' => 'resolved',
        ]);

        $this->assertDatabaseHas('complaint_timeline', [
            'complaint_id' => $complaintId,
            'event_type' => 'resolved',
        ]);

        // -------------------------------------------------------------
        // STEP 6: Citizen Closes the Resolved Complaint
        // -------------------------------------------------------------
        $citizenUser = User::find($citizenId);
        $closeResponse = $this->actingAs($citizenUser, 'sanctum')
            ->patchJson("/api/v1/complaints/{$complaintId}/status", [
                'status' => 'closed',
                'notes' => 'شكراً جزيلاً لسرعة الاستجابة وتم التأكد من حل المشكلة بالموقع.',
            ]);

        $closeResponse->assertStatus(200);
        $this->assertEquals('closed', $closeResponse->json('data.status'));

        $this->assertDatabaseHas('complaints', [
            'id' => $complaintId,
            'status' => 'closed',
        ]);

        // -------------------------------------------------------------
        // STEP 7: Community Project Contribution Flow
        // -------------------------------------------------------------
        $project = Project::create([
            'title' => 'مشروع إنارة الشوارع بالطاقة الشمسية',
            'description' => 'تركيب 200 وحدة إنارة ذكية بالطاقة المتجددة في الشوارع الفرعية.',
            'department_id' => $category->department_id,
            'target_amount' => 100000.00,
            'current_amount' => 25000.00,
            'status' => 'active',
            'latitude' => 15.369445,
            'longitude' => 44.191007,
            'created_by' => $admin->id,
        ]);

        $contributionResponse = $this->actingAs($citizenUser, 'sanctum')
            ->postJson("/api/v1/projects/{$project->id}/contribute", [
                'amount' => 2500.50,
                'payment_method' => 'mada',
            ]);

        $contributionResponse->assertStatus(201);
        $this->assertEquals(2500.50, $contributionResponse->json('data.amount'));

        // Verify high-precision balance: 25000.00 + 2500.50 = 27500.50
        $this->assertEquals('27500.50', (string) $project->fresh()->current_amount);
        $this->assertDatabaseHas('project_contributions', [
            'project_id' => $project->id,
            'contributor_id' => $citizenId,
            'amount' => 2500.50,
        ]);
    }
}
