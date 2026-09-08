<?php

namespace Tests\Feature\Api;

use App\Models\Department;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\MinistryDepartmentSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $citizen;
    protected Department $department;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(MinistryDepartmentSeeder::class);

        $this->department = Department::first();

        // Citizen
        $this->citizen = User::factory()->create(['is_active' => true]);
        $this->citizen->roles()->attach(Role::where('name', 'Citizen')->first()->id);

        // Project
        $this->project = Project::create([
            'title' => 'مشروع تشجير وتأهيل الحدائق العامة',
            'description' => 'تأهيل المساحات الخضراء وزراعة 5000 شجرة لتحسين البيئة الحضرية.',
            'department_id' => $this->department->id,
            'target_amount' => 50000.00,
            'current_amount' => 10000.00,
            'status' => 'active',
            'latitude' => 15.369445,
            'longitude' => 44.191007,
            'created_by' => $this->citizen->id,
        ]);
    }

    public function test_anyone_can_browse_projects_list(): void
    {
        $response = $this->getJson('/api/v1/projects');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'title', 'target_amount', 'current_amount', 'progress_percentage', 'status'],
                ],
            ]);
    }

    public function test_anyone_can_view_project_details(): void
    {
        $response = $this->getJson("/api/v1/projects/{$this->project->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $this->project->id)
            ->assertJsonPath('data.title', $this->project->title);
    }

    public function test_citizen_can_contribute_to_project_with_financial_accuracy(): void
    {
        $contributionAmount = 500.50;

        $response = $this->actingAs($this->citizen, 'sanctum')
            ->postJson("/api/v1/projects/{$this->project->id}/contribute", [
                'amount' => $contributionAmount,
                'payment_method' => 'mada',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.amount', $contributionAmount);

        // Check accurate financial balance: 10000.00 + 500.50 = 10500.50
        $this->assertEquals('10500.50', (string) $this->project->fresh()->current_amount);

        $this->assertDatabaseHas('project_contributions', [
            'project_id' => $this->project->id,
            'contributor_id' => $this->citizen->id,
            'amount' => $contributionAmount,
        ]);

        // Verify side-effect notification
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->citizen->id,
            'type' => 'project_contribution_success',
        ]);
    }
}
