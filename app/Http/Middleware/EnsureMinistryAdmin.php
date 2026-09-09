<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMinistryAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->hasRole('Ministry Admin')) {
            abort(403, 'عذراً، هذا المسار مخصص لمشرفي الوزارات والجهات الحكومية فقط.');
        }

        if (!$user->is_active) {
            abort(403, 'حسابك معطل حالياً. يرجى التواصل مع مسؤول النظام.');
        }

        if (!$user->department_id) {
            abort(403, 'لم يتم تعيين جهة أو إدارة حكومية لهذا الحساب بعد.');
        }

        return $next($request);
    }
}
