<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->hasRole('Super Admin')) {
            abort(403, 'عذراً، هذا المسار مخصص للإدارة العامة للنظام فقط.');
        }

        if (!$user->is_active) {
            abort(403, 'حسابك معطل حالياً. يرجى التواصل مع الدعم الفني.');
        }

        return $next($request);
    }
}
