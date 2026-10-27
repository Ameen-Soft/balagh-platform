<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user() ?? Auth::guard('sanctum')->user();

        if ($user && (! $user->is_active || ! $user->fresh()?->is_active)) {
            // Check if request is API or expects JSON
            if ($request->is('api/*') || $request->expectsJson()) {
                // Invalidate current API token immediately
                $user->currentAccessToken()?->delete();

                return response()->json([
                    'success' => false,
                    'message' => 'تم تعطيل هذا الحساب. يرجى التواصل مع إدارة المنصة.',
                ], 403);
            }

            // Web session termination
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'تم تعطيل هذا الحساب. يرجى التواصل مع إدارة المنصة.']);
        }

        return $next($request);
    }
}
