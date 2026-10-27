<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Ministry;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    /**
     * Test that a user without any team (current_team_id is null and no team records)
     * can load /dashboard with HTTP 200 without encountering the currentTeam 500 error.
     */
    public function test_user_without_team_can_view_dashboard_successfully(): void
    {
        $citizenRole = Role::where('name', 'Citizen')->firstOrFail();

        $user = User::factory()->create([
            'current_team_id' => null,
            'is_active' => true,
        ]);
        $user->roles()->sync([$citizenRole->id]);

        // Assert strictly that user has no team associations in DB
        $this->assertNull($user->current_team_id);
        $this->assertEquals(0, Team::where('user_id', $user->id)->count());

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
        // Assert that the page rendered cleanly without error
        $response->assertSeeText('Dashboard');
    }

    /**
     * Test that registering via standard web registration creates a citizen without a team,
     * assigns the Citizen role, and can immediately view /dashboard.
     */
    public function test_newly_registered_web_user_gets_citizen_role_no_team_and_can_access_dashboard(): void
    {
        $response = $this->post('/register', [
            'name' => 'مواطن تجريبي',
            'email' => 'citizen.test@balagh.gov.ye',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'citizen.test@balagh.gov.ye')->firstOrFail();

        // Must NOT have a team created or assigned
        $this->assertNull($user->current_team_id);
        $this->assertEquals(0, Team::where('user_id', $user->id)->count());

        // Must have Citizen role only
        $this->assertTrue($user->hasRole('Citizen'));
        $this->assertFalse($user->hasRole('Super Admin'));
        $this->assertFalse($user->hasRole('Ministry Admin'));

        // Mark email verified to satisfy 'verified' middleware on /dashboard
        $user->markEmailAsVerified();

        // Dashboard must load with HTTP 200
        $dashboardResponse = $this->actingAs($user)->get('/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertViewIs('dashboard');
    }

    /**
     * Test that Super Admin accessing /dashboard is redirected to admin.dashboard.
     */
    public function test_super_admin_accessing_dashboard_is_redirected_to_admin_dashboard(): void
    {
        $superAdminRole = Role::where('name', 'Super Admin')->firstOrFail();

        $admin = User::factory()->create([
            'current_team_id' => null,
            'is_active' => true,
        ]);
        $admin->roles()->sync([$superAdminRole->id]);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertRedirect(route('admin.dashboard'));
    }

    /**
     * Test that Ministry Admin accessing /dashboard is redirected to ministry.dashboard.
     */
    public function test_ministry_admin_accessing_dashboard_is_redirected_to_ministry_dashboard(): void
    {
        $ministryAdminRole = Role::where('name', 'Ministry Admin')->firstOrFail();

        $ministry = Ministry::create([
            'name' => 'وزارة الاتصالات',
            'code' => 'MTC',
            'logo' => 'logos/mtc.png',
            'contact_email' => 'contact@mtc.gov.ye',
            'is_active' => true,
        ]);

        $department = Department::create([
            'ministry_id' => $ministry->id,
            'name' => 'خدمة العملاء',
            'description' => 'إدارة خدمة العملاء واستقبال البلاغات',
            'is_active' => true,
        ]);

        $ministryUser = User::factory()->create([
            'current_team_id' => null,
            'department_id' => $department->id,
            'is_active' => true,
        ]);
        $ministryUser->roles()->sync([$ministryAdminRole->id]);

        $response = $this->actingAs($ministryUser)->get('/dashboard');

        $response->assertRedirect(route('ministry.dashboard'));
    }
}
