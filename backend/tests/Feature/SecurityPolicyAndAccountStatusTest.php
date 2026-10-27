<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\Department;
use App\Models\Ministry;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityPolicyAndAccountStatusTest extends TestCase
{
    use RefreshDatabase;

    protected User $citizen;

    protected User $inactiveUser;

    protected Department $department;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $citizenRole = Role::where('name', 'Citizen')->firstOrFail();

        $ministry = Ministry::create([
            'name' => 'وزارة النقل',
            'code' => 'MOT',
            'logo' => 'logos/mot.png',
            'contact_email' => 'contact@mot.gov.ye',
            'is_active' => true,
        ]);

        $this->department = Department::create([
            'ministry_id' => $ministry->id,
            'name' => 'إدارة الطرق والنقل البري',
            'description' => 'إشراف وتنسيق مشاريع النقل',
            'is_active' => true,
        ]);

        $this->citizen = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $this->citizen->roles()->sync([$citizenRole->id]);

        $this->inactiveUser = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => false,
            'password' => bcrypt('password123'),
        ]);
        $this->inactiveUser->roles()->sync([$citizenRole->id]);
    }

    /**
     * 1. Inactive account cannot authenticate through standard web login.
     */
    public function test_inactive_account_cannot_login_via_web_form(): void
    {
        $response = $this->post('/login', [
            'email' => $this->inactiveUser->email,
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['email']);
        $this->assertStringContainsString(
            'تم تعطيل هذا الحساب',
            session('errors')->first('email')
        );
    }

    /**
     * 2. Inactive account cannot authenticate through API login.
     */
    public function test_inactive_account_cannot_login_via_api(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $this->inactiveUser->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
        $this->assertStringContainsString(
            'تم تعطيل هذا الحساب',
            $response->json('errors.email.0')
        );
    }

    /**
     * 3. An active web session is immediately invalidated if user is deactivated mid-session.
     */
    public function test_active_web_session_is_terminated_immediately_when_user_is_deactivated(): void
    {
        $superAdminRole = Role::where('name', 'Super Admin')->firstOrFail();
        $admin = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $admin->roles()->sync([$superAdminRole->id]);

        // User starts active
        $this->actingAs($admin, 'web');

        $response = $this->get('/admin/dashboard');
        $response->assertStatus(200);

        // User is deactivated in background
        $admin->update(['is_active' => false]);

        // Subsequent request must be intercepted by EnsureAccountIsActive
        $subsequentResponse = $this->get('/admin/dashboard');

        $subsequentResponse->assertRedirect(route('login'));
        $this->assertGuest('web');
    }

    /**
     * 4. An API token is revoked and returns 403 if user account is deactivated mid-usage.
     */
    public function test_api_token_is_revoked_and_returns_403_when_account_is_deactivated(): void
    {
        $token = $this->citizen->createToken('test_token')->plainTextToken;

        // 1. Initial request with active account succeeds
        $firstResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/auth/me');
        $firstResponse->assertStatus(200);

        // 2. Deactivate citizen
        $this->citizen->update(['is_active' => false]);

        // 3. Next request should fail with 403 and token must be revoked
        $secondResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/auth/me');

        $secondResponse->assertStatus(403);
        $secondResponse->assertJson([
            'success' => false,
            'message' => 'تم تعطيل هذا الحساب. يرجى التواصل مع إدارة المنصة.',
        ]);

        // Token was deleted from DB
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $this->citizen->id,
            'name' => 'test_token',
        ]);

        // Reset auth guards to simulate clean incoming request lifecycle
        app('auth')->forgetGuards();

        // Subsequent request is unauthenticated because token was revoked
        $thirdResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/auth/me');
        $thirdResponse->assertStatus(401);
    }

    /**
     * 5. Citizen cannot access /admin routes (GET and mutation POST).
     */
    public function test_citizen_is_blocked_with_403_from_all_admin_routes(): void
    {
        $this->actingAs($this->citizen);

        // GET admin dashboard
        $this->get(route('admin.dashboard'))->assertStatus(403);

        // GET admin users
        $this->get(route('admin.users.index'))->assertStatus(403);

        // POST admin users
        $this->post(route('admin.users.store'), [
            'name' => 'Unauthorized User',
            'email' => 'unauth@test.com',
            'password' => 'password123',
            'role' => 'Super Admin',
        ])->assertStatus(403);

        // POST admin ministries
        $this->post(route('admin.ministries.store'), [
            'name' => 'وزارة غير مصرح بها',
            'code' => 'UNAUTH',
        ])->assertStatus(403);
    }

    /**
     * 6. Citizen cannot access /ministry routes (GET and mutation POST).
     */
    public function test_citizen_is_blocked_with_403_from_all_ministry_routes(): void
    {
        $this->actingAs($this->citizen);

        // GET ministry dashboard
        $this->get(route('ministry.dashboard'))->assertStatus(403);

        // GET ministry complaints
        $this->get(route('ministry.complaints.index'))->assertStatus(403);

        // POST ministry departments
        $this->post(route('ministry.departments.store'), [
            'name' => 'إدارة جديدة غير مصرح بها',
            'description' => 'اختراق الصلاحيات',
        ])->assertStatus(403);
    }

    /**
     * 7. Citizen cannot perform administrative operations via API.
     */
    public function test_citizen_cannot_perform_admin_operations_via_api(): void
    {
        $token = $this->citizen->createToken('citizen_token')->plainTextToken;

        // Attempt to create a developmental project (requires projects.create or Ministry Admin)
        $projectResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/projects', [
                'department_id' => $this->department->id,
                'title' => 'مشروع غير مصرح به',
                'description' => 'محاولة اختراق الصلاحيات',
                'target_amount' => 500000,
                'latitude' => 15.3500,
                'longitude' => 44.2000,
                'status' => 'planning',
            ]);

        $projectResponse->assertStatus(403);
    }

    /**
     * 8. Mass assignment protection: Web registration cannot elevate roles.
     */
    public function test_web_registration_does_not_permit_role_elevation(): void
    {
        $this->post('/register', [
            'name' => 'هاكر تجريبي',
            'email' => 'hacker.web@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'Super Admin',
            'roles' => ['Super Admin', 'Ministry Admin'],
            'department_id' => $this->department->id,
            'is_active' => false,
            'google_id' => 'fake_google_id_123',
        ]);

        $user = User::where('email', 'hacker.web@test.com')->firstOrFail();

        // Must strictly be a Citizen only
        $this->assertTrue($user->hasRole('Citizen'));
        $this->assertFalse($user->hasRole('Super Admin'));
        $this->assertFalse($user->hasRole('Ministry Admin'));
        $this->assertNull($user->department_id);
    }

    /**
     * 9. Mass assignment protection: API registration cannot elevate roles.
     */
    public function test_api_registration_does_not_permit_role_elevation(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'هاكر تجريبي API',
            'email' => 'hacker.api@test.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'Super Admin',
            'roles' => ['Super Admin', 'Ministry Admin'],
            'department_id' => $this->department->id,
            'google_id' => 'fake_google_id_456',
        ]);

        $response->assertStatus(201);

        $user = User::where('email', 'hacker.api@test.com')->firstOrFail();

        // Must strictly be a Citizen only
        $this->assertTrue($user->hasRole('Citizen'));
        $this->assertFalse($user->hasRole('Super Admin'));
        $this->assertFalse($user->hasRole('Ministry Admin'));
        $this->assertNull($user->department_id);
    }
}
