<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class GoogleWebAuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Authenticate a web session using Google Identity Services (GIS) credential.
     *
     * @param  Request  $request
     * @return JsonResponse
     *
     * @throws ValidationException
     */
    public function handle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'credential' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ], [
            'credential.required' => 'رمز المصادقة من Google مطلوب.',
        ]);

        $webClientId = config('services.google.web_client_id') ?: config('services.google.client_id');

        $user = $this->authService->resolveGoogleUser(
            $validated['credential'],
            $webClientId ?: ''
        );

        // Authenticate into the Web guard
        Auth::guard('web')->login($user, $request->boolean('remember', false));

        // Regenerate session to prevent session fixation attacks
        $request->session()->regenerate();

        // Clear any stale intended URLs that might point to wrong or unauthorized portals
        $request->session()->forget('url.intended');

        // Determine destination dashboard based on role
        $redirectUrl = route('dashboard');
        if ($user->hasRole('Super Admin')) {
            $redirectUrl = route('admin.dashboard');
        } elseif ($user->hasRole('Ministry Admin')) {
            $redirectUrl = route('ministry.dashboard');
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح.',
            'redirect' => $redirectUrl,
        ]);
    }
}
