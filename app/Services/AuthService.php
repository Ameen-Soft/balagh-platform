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

        // Send Email Verification Notification
        try {
            $user->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            Log::warning('Failed to send verification email upon registration: ' . $e->getMessage());
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
     * Update authenticated user profile details.
     *
     * @param  User  $user
     * @param  array<string, mixed>  $data
     * @return User
     */
    public function updateProfile(User $user, array $data): User
    {
        $user->update([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'national_id' => $data['national_id'] ?? null,
        ]);

        return $this->me($user->fresh());
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
            $updates = [];
            if (empty($user->google_id)) {
                $updates['google_id'] = $googleId;
            }
            if (empty($user->email_verified_at)) {
                $updates['email_verified_at'] = now();
            }
            if (! empty($updates)) {
                $user->update($updates);
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
                'email_verified_at' => now(),
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

    /**
     * Resend verification notification to the user.
     *
     * @throws ValidationException
     */
    public function resendVerificationEmail(User $user): void
    {
        if ($user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => ['البريد الإلكتروني تم التحقق منه مسبقاً.'],
            ]);
        }

        try {
            $user->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            Log::error('Failed to resend verification email: ' . $e->getMessage());
            throw ValidationException::withMessages([
                'email' => ['تعذر إرسال رسالة التحقق حالياً، يرجى المحاولة بعد قليل.'],
            ]);
        }
    }

    /**
     * Verify email via signed URL parameters.
     *
     * @return array{user: User, already_verified: bool}
     *
     * @throws ValidationException
     */
    public function verifyEmail(int $id, string $hash): array
    {
        $user = User::find($id);

        if (! $user || ! hash_equals(sha1($user->getEmailForVerification()), (string) $hash)) {
            throw ValidationException::withMessages([
                'signature' => ['رابط التحقق غير صالح أو منتهي الصلاحية.'],
            ]);
        }

        if ($user->hasVerifiedEmail()) {
            return [
                'user' => $user,
                'already_verified' => true,
            ];
        }

        $user->markEmailAsVerified();
        event(new \Illuminate\Auth\Events\Verified($user));

        return [
            'user' => $user,
            'already_verified' => false,
        ];
    }

    /**
     * Send password reset link to user email without revealing user existence.
     */
    public function sendPasswordResetLink(string $email): void
    {
        $user = User::where('email', $email)->first();

        if ($user && $user->is_active) {
            try {
                $token = \Illuminate\Support\Facades\Password::broker()->createToken($user);
                $user->sendPasswordResetNotification($token);
            } catch (\Throwable $e) {
                Log::error('Failed to send password reset email: ' . $e->getMessage());
            }
        }
    }

    /**
     * Reset user password using token and credentials.
     *
     * @param  array<string, mixed>  $credentials
     *
     * @throws ValidationException
     */
    public function resetPassword(array $credentials): void
    {
        $status = \Illuminate\Support\Facades\Password::broker()->reset(
            $credentials,
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new \Illuminate\Auth\Events\PasswordReset($user));
            }
        );

        if ($status !== \Illuminate\Support\Facades\Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }
    }
}

