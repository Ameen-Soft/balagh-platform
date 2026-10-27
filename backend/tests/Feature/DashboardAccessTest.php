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
     * Test that an admin user without any team (current_team_id is null and no team records)
     * can load /dashboard without encountering the currentTeam 500 error and redirects to admin dashboard.
     */
    public function test_admin_without_team_can_access_dashboard_successfully(): void
    {
        $superAdminRole = Role::where('name', 'Super Admin')->firstOrFail();

        $user = User::factory()->create([
            'current_team_id' => null,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $user->roles()->sync([$superAdminRole->id]);

        // Assert strictly that user has no team associations in DB
        $this->assertNull($user->current_team_id);
        $this->assertEquals(0, Team::where('user_id', $user->id)->count());

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('admin.dashboard'));
    }

    /**
     * Test that a citizen attempting to access the web /dashboard is logged out
     * and redirected to the login page with guidance to use the mobile app.
     */
    public function test_citizen_accessing_web_dashboard_is_logged_out_and_redirected_to_login(): void
    {
        $citizenRole = Role::where('name', 'Citizen')->firstOrFail();

        $citizen = User::factory()->create([
            'current_team_id' => null,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $citizen->roles()->sync([$citizenRole->id]);

        $response = $this->actingAs($citizen)->get('/dashboard');

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest('web');
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
