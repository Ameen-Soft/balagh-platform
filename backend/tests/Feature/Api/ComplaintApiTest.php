<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\MinistryDepartmentSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComplaintApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $citizen;
    protected User $ministryAdmin;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(MinistryDepartmentSeeder::class);
        $this->seed(CategorySeeder::class);

        // Create citizen user
        $this->citizen = User::factory()->create(['is_active' => true]);
        $citizenRole = Role::where('name', 'Citizen')->first();
        $this->citizen->roles()->attach($citizenRole->id);

        // Fetch category
        $this->category = Category::whereNotNull('department_id')->first();

        // Create ministry admin belonging to the department's ministry
        $this->ministryAdmin = User::factory()->create([
            'department_id' => $this->category->department_id,
            'is_active' => true,
        ]);
        $adminRole = Role::where('name', 'Ministry Admin')->first();
        $this->ministryAdmin->roles()->attach($adminRole->id);
    }

    public function test_citizen_can_create_complaint_with_auto_routing(): void
    {
        $payload = [
            'title' => 'هبوط إسفلتي مفاجئ في الشارع الرئيسي',
            'description' => 'يوجد هبوط إسفلتي خطير يعيق حركة المرور ويتسبب في أضرار للمركبات.',
            'category_id' => $this->category->id,
            'latitude' => 15.369445,
            'longitude' => 44.191007,
            'priority' => 'high',
        ];

        $response = $this->actingAs($this->citizen, 'sanctum')
            ->postJson('/api/v1/complaints', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', $payload['title'])
            ->assertJsonPath('data.status', 'new')
            ->assertJsonPath('data.department.id', $this->category->department_id);

        $this->assertDatabaseHas('complaints', [
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->category->department_id,
            'status' => 'new',
        ]);

        $complaint = Complaint::where('citizen_id', $this->citizen->id)->first();
        $this->assertDatabaseHas('complaint_timeline', [
            'complaint_id' => $complaint->id,
            'event_type' => 'created',
        ]);

        // Notification side-effect check
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->citizen->id,
            'type' => 'complaint_created',
        ]);
    }

    public function test_duplicate_complaint_detection_links_to_original(): void
    {
        // 1. Original complaint
        $original = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->category->department_id,
            'title' => 'ماس كهربائي في عمود الإنارة بجوار المدرسة',
            'description' => 'يوجد سلك مكشوف يتدلى من عمود الإنارة ويسبب شرراً كهربائياً مستمراً.',
            'status' => 'new',
            'priority' => 'urgent',
            'latitude' => 15.350000,
            'longitude' => 44.200000,
        ]);

        // 2. Second complaint submitted by another citizen in same location and similar text
        $secondCitizen = User::factory()->create(['is_active' => true]);
        $secondCitizen->roles()->attach(Role::where('name', 'Citizen')->first()->id);

        $payload = [
            'title' => 'عمود إنارة به ماس كهربائي خطير',
            'description' => 'يوجد سلك مكشوف يتدلى من عمود الإنارة ويسبب شرراً كهربائياً مستمراً.',
            'category_id' => $this->category->id,
            'latitude' => 15.350100, // Very close (~15 meters)
            'longitude' => 44.200050,
            'priority' => 'urgent',
        ];

        $response = $this->actingAs($secondCitizen, 'sanctum')
            ->postJson('/api/v1/complaints', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.duplicate_of_id', $original->id);

        $this->assertDatabaseHas('complaints', [
            'citizen_id' => $secondCitizen->id,
            'duplicate_of_id' => $original->id,
        ]);
    }

    public function test_ministry_admin_can_update_complaint_status(): void
    {
        $complaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->category->department_id,
            'title' => 'تسرب مياه في الحي الشرقي',
            'description' => 'انكسار أنبوب مياه رئيسي يغمر الشارع.',
            'status' => 'new',
            'priority' => 'medium',
            'latitude' => 15.340000,
            'longitude' => 44.210000,
        ]);

        $response = $this->actingAs($this->ministryAdmin, 'sanctum')
            ->patchJson("/api/v1/complaints/{$complaint->id}/status", [
                'status' => 'under_review',
                'notes' => 'جاري تدقيق الشكوى وتحديد الفريق الميداني المختص.',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'under_review');

        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'status' => 'under_review',
        ]);

        $this->assertDatabaseHas('complaint_timeline', [
            'complaint_id' => $complaint->id,
            'event_type' => 'status_changed',
            'new_value' => 'under_review',
        ]);
    }

    public function test_citizen_cannot_update_complaint_status(): void
    {
        $complaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->category->department_id,
            'title' => 'حفرة في الحي',
            'description' => 'حفرة عميقة في الشارع الفرعي.',
            'status' => 'new',
            'priority' => 'low',
            'latitude' => 15.340000,
            'longitude' => 44.210000,
        ]);

        $response = $this->actingAs($this->citizen, 'sanctum')
            ->patchJson("/api/v1/complaints/{$complaint->id}/status", [
                'status' => 'resolved',
            ]);

        $response->assertStatus(403);
    }
}
