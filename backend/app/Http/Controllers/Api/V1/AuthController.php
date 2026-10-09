<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
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
}
