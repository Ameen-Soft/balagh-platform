<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Services\GoogleTokenVerifierService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        // Ensure google client IDs are set in config for testing
        config([
            'services.google.client_id' => 'test-google-client-id.apps.googleusercontent.com',
            'services.google.web_client_id' => 'test-google-web-client-id.apps.googleusercontent.com',
            'services.google.android_client_id' => 'test-google-android-client-id.apps.googleusercontent.com',
        ]);
    }

    public function test_login_page_renders_google_identity_services_elements(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('g_id_onload');
        $response->assertSee('g_id_signin');
        $response->assertSee('handleGoogleSignIn');
        $response->assertSee('google-auth-error');
        $response->assertSee('https://accounts.google.com/gsi/client');
    }

    public function test_google_login_registers_new_user_as_citizen_and_logs_in_to_web_session(): void
    {
        $mockVerifier = Mockery::mock(GoogleTokenVerifierService::class);
        $mockVerifier->shouldReceive('verify')
            ->once()
            ->with('valid-google-jwt-new-user', 'test-google-web-client-id.apps.googleusercontent.com')
            ->andReturn([
                'sub' => 'google-user-123456',
                'email' => 'citizen.ahmed@example.test',
                'name' => 'أحمد المواطن',
                'email_verified' => true,
                'aud' => 'test-google-web-client-id.apps.googleusercontent.com',
            ]);
        $this->app->instance(GoogleTokenVerifierService::class, $mockVerifier);

        $response = $this->postJson(route('login.google'), [
            'credential' => 'valid-google-jwt-new-user',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'redirect' => route('dashboard'),
        ]);

        // Verify user was created in the database
        $this->assertDatabaseHas('users', [
            'email' => 'citizen.ahmed@example.test',
            'google_id' => 'google-user-123456',
            'name' => 'أحمد المواطن',
            'is_active' => true,
        ]);

        $user = User::where('email', 'citizen.ahmed@example.test')->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->hasRole('Citizen'));
        $this->assertFalse($user->hasRole('Super Admin'));

        // Verify web session
        $this->assertAuthenticatedAs($user, 'web');
    }

    public function test_google_login_authenticates_existing_linked_user_and_redirects_based_on_role(): void
    {
        $superAdminRole = Role::where('name', 'Super Admin')->firstOrFail();

        $admin = User::factory()->create([
            'email' => 'admin.salem@example.test',
            'google_id' => 'google-admin-999',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $admin->roles()->sync([$superAdminRole->id]);

        $mockVerifier = Mockery::mock(GoogleTokenVerifierService::class);
        $mockVerifier->shouldReceive('verify')
            ->once()
            ->with('valid-google-jwt-admin', 'test-google-web-client-id.apps.googleusercontent.com')
            ->andReturn([
                'sub' => 'google-admin-999',
                'email' => 'admin.salem@example.test',
                'name' => 'سالم المدير العام',
                'email_verified' => true,
                'aud' => 'test-google-web-client-id.apps.googleusercontent.com',
            ]);
        $this->app->instance(GoogleTokenVerifierService::class, $mockVerifier);

        $response = $this->postJson(route('login.google'), [
            'credential' => 'valid-google-jwt-admin',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'redirect' => route('admin.dashboard'),
        ]);

        $this->assertAuthenticatedAs($admin, 'web');
    }

    public function test_google_login_rejects_unverified_google_email(): void
    {
        $mockVerifier = Mockery::mock(GoogleTokenVerifierService::class);
        $mockVerifier->shouldReceive('verify')
            ->once()
            ->andReturn([
                'sub' => 'google-unverified-sub',
                'email' => 'unverified@example.test',
                'email_verified' => false,
            ]);
        $this->app->instance(GoogleTokenVerifierService::class, $mockVerifier);

        $response = $this->postJson(route('login.google'), [
            'credential' => 'jwt-with-unverified-email',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
        $this->assertStringContainsString('لم يتم التحقق من ملكية البريد الإلكتروني لدى Google', $response->json('message') . $response->json('errors.email.0'));

        $this->assertGuest('web');
        $this->assertDatabaseMissing('users', [
            'email' => 'unverified@example.test',
        ]);
    }

    public function test_google_login_rejects_auto_linking_when_existing_account_has_same_email_without_google_id(): void
    {
        $citizenRole = Role::where('name', 'Citizen')->firstOrFail();

        $existingUser = User::factory()->create([
            'email' => 'existing.unlinked@example.test',
            'google_id' => null,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $existingUser->roles()->sync([$citizenRole->id]);

        $mockVerifier = Mockery::mock(GoogleTokenVerifierService::class);
        $mockVerifier->shouldReceive('verify')
            ->once()
            ->andReturn([
                'sub' => 'google-new-incoming-sub',
                'email' => 'existing.unlinked@example.test',
                'email_verified' => true,
            ]);
        $this->app->instance(GoogleTokenVerifierService::class, $mockVerifier);

        $response = $this->postJson(route('login.google'), [
            'credential' => 'jwt-for-unlinked-account',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
        $this->assertStringContainsString('يوجد حساب مسجل بهذا البريد الإلكتروني. يرجى تسجيل الدخول بكلمة المرور أولاً', $response->json('errors.email.0'));

        // Must NOT have linked the google_id automatically
        $this->assertNull($existingUser->fresh()->google_id);
        $this->assertGuest('web');
    }

    public function test_google_login_rejects_and_logs_critical_conflict_when_sub_and_email_belong_to_different_users(): void
    {
        $citizenRole = Role::where('name', 'Citizen')->firstOrFail();

        $userA = User::factory()->create([
            'email' => 'usera@example.test',
            'google_id' => 'conflicting-google-sub',
            'is_active' => true,
        ]);
        $userA->roles()->sync([$citizenRole->id]);

        $userB = User::factory()->create([
            'email' => 'userb@example.test',
            'google_id' => 'other-google-sub',
            'is_active' => true,
        ]);
        $userB->roles()->sync([$citizenRole->id]);

        // Google token has sub belonging to User A, but email belonging to User B
        $mockVerifier = Mockery::mock(GoogleTokenVerifierService::class);
        $mockVerifier->shouldReceive('verify')
            ->once()
            ->andReturn([
                'sub' => 'conflicting-google-sub',
                'email' => 'userb@example.test',
                'email_verified' => true,
            ]);
        $this->app->instance(GoogleTokenVerifierService::class, $mockVerifier);

        $response = $this->postJson(route('login.google'), [
            'credential' => 'conflicting-jwt',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
        $this->assertStringContainsString('تعارض في بيانات الهوية الرقمية', $response->json('errors.email.0'));

        $this->assertGuest('web');
    }

    public function test_google_login_rejects_deactivated_account(): void
    {
        $citizenRole = Role::where('name', 'Citizen')->firstOrFail();

        $bannedUser = User::factory()->create([
            'email' => 'banned@example.test',
            'google_id' => 'google-banned-sub',
            'is_active' => false,
        ]);
        $bannedUser->roles()->sync([$citizenRole->id]);

        $mockVerifier = Mockery::mock(GoogleTokenVerifierService::class);
        $mockVerifier->shouldReceive('verify')
            ->once()
            ->andReturn([
                'sub' => 'google-banned-sub',
                'email' => 'banned@example.test',
                'email_verified' => true,
            ]);
        $this->app->instance(GoogleTokenVerifierService::class, $mockVerifier);

        $response = $this->postJson(route('login.google'), [
            'credential' => 'jwt-for-banned-user',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
        $this->assertStringContainsString('تم تعطيل هذا الحساب', $response->json('errors.email.0'));

        $this->assertGuest('web');
    }

    public function test_google_login_validates_credential_is_required(): void
    {
        $response = $this->postJson(route('login.google'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['credential']);
    }

    public function test_mobile_api_google_login_issues_sanctum_token(): void
    {
        $mockVerifier = Mockery::mock(GoogleTokenVerifierService::class);
        $mockVerifier->shouldReceive('verify')
            ->once()
            ->with(
                'valid-mobile-jwt',
                Mockery::subset([
                    'test-google-client-id.apps.googleusercontent.com',
                    'test-google-android-client-id.apps.googleusercontent.com',
                ])
            )
            ->andReturn([
                'sub' => 'google-mobile-sub-555',
                'email' => 'mobile.citizen@example.test',
                'name' => 'مواطن الموبايل',
                'email_verified' => true,
            ]);
        $this->app->instance(GoogleTokenVerifierService::class, $mockVerifier);

        $response = $this->postJson('/api/v1/auth/google', [
            'id_token' => 'valid-mobile-jwt',
            'device_name' => 'Pixel 8',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'user',
                'token',
            ],
            'message',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'mobile.citizen@example.test',
            'google_id' => 'google-mobile-sub-555',
        ]);
    }

    public function test_google_web_login_is_throttled_against_rapid_requests(): void
    {
        // 5 requests allowed per minute by 'throttle:login'
        for ($i = 0; $i < 5; $i++) {
            $this->postJson(route('login.google'), []);
        }

        // 6th request must be throttled with HTTP 429
        $response = $this->postJson(route('login.google'), []);
        $response->assertStatus(429);
    }
}
