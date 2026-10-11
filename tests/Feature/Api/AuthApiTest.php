<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_citizen_can_register_and_receive_token(): void
    {
        $payload = [
            'name' => 'محمد أحمد',
            'email' => 'citizen.test@balagh.gov.ye',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'phone' => '777123456',
            'national_id' => '1002003004',
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email', 'roles'],
                    'token',
                ],
            ]);

        $this->assertDatabaseHas('users', ['email' => 'citizen.test@balagh.gov.ye']);

        $user = User::where('email', 'citizen.test@balagh.gov.ye')->first();
        $this->assertTrue($user->hasRole('Citizen'));
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'login.test@balagh.gov.ye',
            'password' => bcrypt('Secret123!'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'login.test@balagh.gov.ye',
            'password' => 'Secret123!',
            'device_name' => 'TestDevice',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                ],
            ]);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'wrong.test@balagh.gov.ye',
            'password' => bcrypt('CorrectPassword123!'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'wrong.test@balagh.gov.ye',
            'password' => 'WrongPassword!',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_authenticated_user_can_access_me_endpoint(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_user_can_logout_and_revoke_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertCount(0, $user->fresh()->tokens);
    }

    public function test_authenticated_user_can_update_profile_successfully(): void
    {
        $user = User::factory()->create([
            'name' => 'الاسم القديم',
            'phone' => '770000000',
            'national_id' => '1112223334',
        ]);

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/v1/auth/me', [
            'name' => 'الاسم المحدث',
            'phone' => '771234567',
            'national_id' => '9998887776',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'الاسم المحدث')
            ->assertJsonPath('data.phone', '771234567')
            ->assertJsonPath('data.national_id', '9998887776');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'الاسم المحدث',
            'phone' => '771234567',
            'national_id' => '9998887776',
        ]);
    }

    public function test_update_profile_fails_when_name_is_missing(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/v1/auth/me', [
            'phone' => '771234567',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['errors' => ['name']]);
    }

    public function test_update_profile_rejects_duplicate_national_id_from_other_user(): void
    {
        User::factory()->create([
            'national_id' => '1002003004',
        ]);

        $user = User::factory()->create([
            'national_id' => '9999999999',
        ]);

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/v1/auth/me', [
            'name' => 'مستخدم يحاول تكرار الهوية',
            'national_id' => '1002003004',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['errors' => ['national_id']]);
    }

    public function test_update_profile_allows_keeping_same_national_id(): void
    {
        $user = User::factory()->create([
            'name' => 'الاسم الأول',
            'national_id' => '1002003004',
        ]);

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/v1/auth/me', [
            'name' => 'الاسم الجديد',
            'national_id' => '1002003004',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'الاسم الجديد')
            ->assertJsonPath('data.national_id', '1002003004');
    }

    public function test_update_profile_does_not_allow_modifying_roles_or_email(): void
    {
        $user = User::factory()->create([
            'email' => 'original@balagh.gov.ye',
        ]);
        $originalEmail = $user->email;

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/v1/auth/me', [
            'name' => 'اسم معدل',
            'email' => 'hacked@balagh.gov.ye',
            'roles' => ['Admin', 'Super Admin'],
            'is_active' => false,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.email', $originalEmail);

        $fresh = $user->fresh();
        $this->assertEquals($originalEmail, $fresh->email);
        $this->assertFalse($fresh->hasRole('Admin'));
        $this->assertFalse($fresh->hasRole('Super Admin'));
    }

    public function test_unauthenticated_user_cannot_update_profile(): void
    {
        $response = $this->putJson('/api/v1/auth/me', [
            'name' => 'اسم بدون توكن',
        ]);

        $response->assertStatus(401);
    }
}
