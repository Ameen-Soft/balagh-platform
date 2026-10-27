<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use App\Services\GoogleTokenVerifierService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        protected GoogleTokenVerifierService $googleVerifier
    ) {}
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
            Log::warning('Failed to send verification email upon registration: '.$e->getMessage());
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
     * @param  array<string, mixed>  $data
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
     * Resolve and synchronize a user using Google ID Token with cryptographic verification
     * and atomic account-linking / conflict policies.
     *
     * @param  string  $idToken
     * @param  string|array<int, string>  $expectedAudience
     * @return User
     *
     * @throws ValidationException
     */
    public function resolveGoogleUser(string $idToken, string|array $expectedAudience): User
    {
        $payload = $this->googleVerifier->verify($idToken, $expectedAudience);

        $googleId = $payload['sub'] ?? null;
        $email = $payload['email'] ?? null;
        $emailVerified = $payload['email_verified'] ?? false;

        if (! $googleId || ! $email) {
            throw ValidationException::withMessages([
                'id_token' => ['بيانات الحساب المسترجعة من Google غير مكتملة.'],
            ]);
        }

        if ($emailVerified !== true && $emailVerified !== 'true' && $emailVerified !== 1) {
            throw ValidationException::withMessages([
                'email' => ['لم يتم التحقق من ملكية البريد الإلكتروني لدى Google.'],
            ]);
        }

        return DB::transaction(function () use ($googleId, $email, $payload) {
            // Lock records for update to prevent race conditions during concurrent authentication
            $userByGoogle = User::where('google_id', $googleId)->lockForUpdate()->first();
            $userByEmail = User::where('email', $email)->lockForUpdate()->first();

            // Conflict Check: Google sub belongs to one user, but Google email matches another user
            if ($userByGoogle && $userByEmail && $userByGoogle->id !== $userByEmail->id) {
                Log::critical("Google identity conflict detected: sub {$googleId} belongs to user ID {$userByGoogle->id}, but email {$email} matches user ID {$userByEmail->id}");
                throw ValidationException::withMessages([
                    'email' => ['تعارض في بيانات الهوية الرقمية، يرجى التواصل مع الدعم الفني.'],
                ]);
            }

            // Case 1: Existing user with matching google_id
            if ($userByGoogle) {
                if (! $userByGoogle->is_active) {
                    throw ValidationException::withMessages([
                        'email' => ['تم تعطيل هذا الحساب. يرجى التواصل مع إدارة المنصة.'],
                    ]);
                }

                if (empty($userByGoogle->email_verified_at)) {
                    $userByGoogle->update(['email_verified_at' => now()]);
                }

                return $userByGoogle;
            }

            // Case 2: Existing user with matching email but NO google_id (or different google_id)
            // Policy: Do NOT auto-link by email alone! User must authenticate with password first to link.
            if ($userByEmail) {
                throw ValidationException::withMessages([
                    'email' => ['يوجد حساب مسجل بهذا البريد الإلكتروني. يرجى تسجيل الدخول بكلمة المرور أولاً ثم ربط حساب Google من الملف الشخصي.'],
                ]);
            }

            // Case 3: Completely new user
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
                $user->roles()->syncWithoutDetaching([$citizenRole->id]);
            }

            return $user;
        });
    }

    /**
     * Authenticate or register a user using Google ID Token for API/Mobile.
     *
     * @return array{user: User, token: string}
     *
     * @throws ValidationException
     */
    public function loginWithGoogle(string $idToken, ?string $deviceName = null): array
    {
        $allowedAudiences = array_filter([
            config('services.google.client_id'),
            config('services.google.android_client_id'),
            config('services.google.web_client_id'),
        ]);

        $user = $this->resolveGoogleUser($idToken, $allowedAudiences);

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
            Log::error('Failed to resend verification email: '.$e->getMessage());
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
        event(new Verified($user));

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
                $token = Password::broker()->createToken($user);
                $user->sendPasswordResetNotification($token);
            } catch (\Throwable $e) {
                Log::error('Failed to send password reset email: '.$e->getMessage());
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
        $status = Password::broker()->reset(
            $credentials,
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }
    }
}
