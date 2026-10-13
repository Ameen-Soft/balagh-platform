<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Register a new citizen user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return $this->successResponse([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'تم إنشاء الحساب بنجاح وتم تسجيل الدخول تلقائياً.', 201);
    }

    /**
     * Log in a user and generate a Sanctum personal access token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->validated('email'),
            $request->validated('password'),
            $request->validated('device_name')
        );

        return $this->successResponse([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'تم تسجيل الدخول بنجاح.');
    }

    /**
     * Log out the current user by revoking their Sanctum token.
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->successResponse(null, 'تم تسجيل الخروج بنجاح.');
    }

    /**
     * Return authenticated user profile details with roles and permissions.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $this->authService->me($request->user());

        return $this->successResponse(new UserResource($user), 'بيانات الملف الشخصي للمستخدم الحالي.');
    }

    /**
     * Update authenticated user profile details.
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->authService->updateProfile(
            $request->user(),
            $request->validated()
        );

        return $this->successResponse(
            new UserResource($user),
            'تم تحديث بيانات الملف الشخصي بنجاح.'
        );
    }

    /**
     * Authenticate or register user via Google OAuth ID token.
     */
    public function googleLogin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_token' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ], [
            'id_token.required' => 'رمز مصادقة Google مطلوب.',
        ]);

        $result = $this->authService->loginWithGoogle(
            $validated['id_token'],
            $validated['device_name'] ?? null
        );

        return $this->successResponse([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'تم تسجيل الدخول بواسطة Google بنجاح.');
    }

    /**
     * Resend the email verification notification.
     */
    public function resendVerification(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user && $request->filled('email')) {
            $user = \App\Models\User::where('email', $request->input('email'))->first();
        }

        if ($user) {
            $this->authService->resendVerificationEmail($user);
        }

        return $this->successResponse(
            null,
            'تم إرسال رابط التحقق من البريد الإلكتروني بنجاح.'
        );
    }

    /**
     * Verify email via signed URL.
     */
    public function verifyEmail(Request $request, string $id, string $hash)
    {
        if (! $request->hasValidSignature()) {
            if ($request->wantsJson()) {
                return $this->errorResponse('رابط التحقق غير صالح أو انتهت صلاحيته.', 403);
            }

            return response(
                $this->renderVerificationHtml(
                    'رابط التحقق غير صالح أو منتهي الصلاحية',
                    'يرجى فتح تطبيق بادر وطلب إرسال رابط تحقق جديد.',
                    false,
                    'error'
                ),
                403
            );
        }

        $result = $this->authService->verifyEmail((int) $id, $hash);

        if ($request->wantsJson()) {
            return $this->successResponse([
                'is_verified' => true,
                'already_verified' => $result['already_verified'],
            ], $result['already_verified'] ? 'تم التحقق من البريد الإلكتروني مسبقاً.' : 'تم التحقق من بريدك الإلكتروني بنجاح.');
        }

        return response(
            $this->renderVerificationHtml(
                $result['already_verified'] ? 'البريد الإلكتروني موثق مسبقاً' : 'تم التحقق بنجاح!',
                'تم تأكيد بريدك الإلكتروني في منصة بادر بنجاح. يمكنك الآن العودة إلى التطبيق.',
                true,
                $result['already_verified'] ? 'already_verified' : 'success'
            )
        );
    }

    /**
     * Get the current email verification status.
     */
    public function getVerificationStatus(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->successResponse([
            'is_verified' => $user->hasVerifiedEmail(),
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at?->toIso8601String(),
        ], 'حالة التحقق من البريد الإلكتروني.');
    }

    /**
     * Send password reset link to user.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'يرجى إدخال البريد الإلكتروني.',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
        ]);

        $this->authService->sendPasswordResetLink($validated['email']);

        // Always return generic success to prevent email enumeration
        return $this->successResponse(
            null,
            'إذا كان هذا البريد مسجلاً لدينا، فستصلك رسالة تحتوي على تعليمات إعادة تعيين كلمة المرور.'
        );
    }

    /**
     * Reset user password with token.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'token.required' => 'رمز إعادة التعيين مطلوب.',
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
            'password.required' => 'كلمة المرور الجديدة مطلوبة.',
            'password.min' => 'كلمة المرور يجب ألا تقل عن 8 أحرف.',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',
        ]);

        $this->authService->resetPassword($validated);

        return $this->successResponse(
            null,
            'تم إعادة تعيين كلمة المرور بنجاح. يمكنك الآن تسجيل الدخول بكلمة المرور الجديدة.'
        );
    }

    /**
     * Helper to render branded Yemen/Badir HTML verification response page with deep linking.
     */
    protected function renderVerificationHtml(string $title, string $message, bool $isSuccess, string $status): string
    {
        $deepLink = "bader://verify-email?status=" . urlencode($status);
        $badgeBg = $isSuccess ? '#059669' : '#CE1126';
        $badgeIcon = $isSuccess ? '&#10003;' : '&#10007;';

        return <<<HTML
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title} - منصة بادر</title>
    <meta http-equiv="refresh" content="3;url={$deepLink}">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Cairo", Tahoma, sans-serif;
            background-color: #F8FAFC;
            color: #08080A;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .card {
            background: #FFFFFF;
            max-width: 440px;
            margin: 20px;
            padding: 36px 28px;
            border-radius: 18px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.06);
            text-align: center;
        }
        .icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background-color: {$badgeBg};
            color: #FFFFFF;
            font-size: 38px;
            line-height: 72px;
            font-weight: bold;
        }
        h1 {
            font-size: 22px;
            font-weight: 800;
            margin-bottom: 12px;
            color: #08080A;
        }
        p {
            font-size: 14px;
            color: #6B7280;
            line-height: 1.6;
            margin-bottom: 26px;
        }
        .btn {
            display: inline-block;
            background-color: #CE1126;
            color: #FFFFFF;
            text-decoration: none;
            padding: 13px 28px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 15px;
            box-shadow: 0 4px 12px rgba(206, 17, 38, 0.25);
            transition: all 0.2s ease;
        }
        .btn:hover {
            background-color: #B70E20;
        }
        .footer {
            margin-top: 24px;
            font-size: 12px;
            color: #9CA3AF;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">{$badgeIcon}</div>
        <h1>{$title}</h1>
        <p>{$message}</p>
        <a href="{$deepLink}" class="btn">العودة إلى تطبيق بادر</a>
        <div class="footer">منصة بادر الوطنية &copy; 2026</div>
    </div>
    <script>
        setTimeout(function() {
            window.location.href = "{$deepLink}";
        }, 1200);
    </script>
</body>
</html>
HTML;
    }
}

