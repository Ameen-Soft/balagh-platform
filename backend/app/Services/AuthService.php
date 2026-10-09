<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Register a new citizen account and issue a Sanctum token.
     *
     * @param  array<string, mixed>  $data
     * @return array{user: User, token: string}
     */
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'national_id' => $data['national_id'] ?? null,
            'is_active' => true,
        ]);

        // Automatically assign 'Citizen' role to new self-registered users
        $citizenRole = Role::where('name', 'Citizen')->first();
        if ($citizenRole) {
            $user->roles()->attach($citizenRole->id);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user->load(['roles', 'department']),
            'token' => $token,
        ];
    }

    /**
     * Authenticate a user and issue a Sanctum token.
     *
     * @return array{user: User, token: string}
     *
     * @throws ValidationException
     */
    public function login(string $email, string $password, ?string $deviceName = null): array
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['البريد الإلكتروني أو كلمة المرور غير صحيحة.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['تم تعطيل هذا الحساب. يرجى التواصل مع إدارة المنصة.'],
            ]);
        }

        $tokenName = $deviceName ?? 'auth_token';
        $token = $user->createToken($tokenName)->plainTextToken;

        return [
            'user' => $user->load(['roles', 'department.ministry']),
            'token' => $token,
        ];
    }

    /**
     * Revoke the user's current access token.
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    /**
     * Get the authenticated user with loaded relations.
     */
    public function me(User $user): User
    {
        return $user->load(['roles.permissions', 'department.ministry']);
    }

    /**
     * Authenticate or register a user using Google ID Token.
     *
     * @return array{user: User, token: string}
     *
     * @throws ValidationException
     */
    public function loginWithGoogle(string $idToken, ?string $deviceName = null): array
    {
        try {
            $response = Http::timeout(10)->get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $idToken,
            ]);
        } catch (\Throwable $e) {
            Log::error('Google token verification connection failed: ' . $e->getMessage());
            throw ValidationException::withMessages([
                'id_token' => ['تعذر الاتصال بخوادم Google للتحقق من الحساب، يرجى المحاولة لاحقاً.'],
            ]);
        }

        if (! $response->successful()) {
            throw ValidationException::withMessages([
                'id_token' => ['رمز تسجيل الدخول من Google غير صالح أو منتهي الصلاحية.'],
            ]);
        }

        $payload = $response->json();

        $allowedClientIds = array_filter([
            config('services.google.client_id'),
            config('services.google.android_client_id'),
        ]);

        $tokenAud = $payload['aud'] ?? null;
        $tokenAzp = $payload['azp'] ?? null;

        $isValidAudience = empty($allowedClientIds) ||
            in_array($tokenAud, $allowedClientIds, true) ||
            in_array($tokenAzp, $allowedClientIds, true);

        if (! $isValidAudience) {
            throw ValidationException::withMessages([
                'id_token' => ['رمز المصادقة غير مخصص لهذا التطبيق.'],
            ]);
        }

        $email = $payload['email'] ?? null;
        $googleId = $payload['sub'] ?? null;

        if (! $email) {
            throw ValidationException::withMessages([
                'id_token' => ['لم يتم العثور على بريد إلكتروني صالح مرتبط بحساب Google.'],
            ]);
        }

        $user = User::where('google_id', $googleId)
            ->orWhere('email', $email)
            ->first();

        if ($user) {
            if (empty($user->google_id)) {
                $user->update(['google_id' => $googleId]);
            }

            if (! $user->is_active) {
                throw ValidationException::withMessages([
                    'email' => ['تم تعطيل هذا الحساب. يرجى التواصل مع إدارة المنصة.'],
                ]);
            }
        } else {
            $name = $payload['name'] ?? explode('@', $email)[0];
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'google_id' => $googleId,
                'password' => Hash::make(Str::random(32)),
                'is_active' => true,
            ]);

            $citizenRole = Role::where('name', 'Citizen')->first();
            if ($citizenRole) {
                $user->roles()->attach($citizenRole->id);
            }
        }

        $tokenName = $deviceName ?? 'google_auth_token';
        $token = $user->createToken($tokenName)->plainTextToken;

        return [
            'user' => $user->load(['roles', 'department.ministry']),
            'token' => $token,
        ];
    }
}
