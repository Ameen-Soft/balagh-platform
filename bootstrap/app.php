<?php

use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\EnsureMinistryAdmin;
use App\Http\Middleware\EnsureSuperAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'super_admin' => EnsureSuperAdmin::class,
            'ministry_admin' => EnsureMinistryAdmin::class,
            'active_user' => EnsureAccountIsActive::class,
        ]);

        $middleware->appendToGroup('web', [
            EnsureAccountIsActive::class,
        ]);

        $middleware->appendToGroup('api', [
            EnsureAccountIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حجم الملف المرفوع كبير جداً ويتجاوز الحد الأقصى المسموح به في إعدادات الخادم (PHP post_max_size / 100MB).',
                ], 413);
            }

            return back()->withErrors(['app_apk_file' => 'حجم الملف كبير جداً ويتجاوز الحد الأقصى المسموح به في السيرفر.']);
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'بيانات الإدخال غير صالحة، يرجى التحقق من الحقول المطلوبة.',
                    'errors' => $e->errors(),
                ], 422);
            }
        });
    })->create();
