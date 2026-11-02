<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    public function __construct(
        protected SettingService $settingService
    ) {}

    /**
     * Display the site settings and head meta management page.
     */
    public function index(Request $request): View
    {
        $this->ensureSuperAdmin($request);

        $groupedSettings = $this->settingService->getGroupedSettings();
        $settings = Setting::getAllCached();

        return view('admin.settings.index', compact('groupedSettings', 'settings'));
    }

    /**
     * Update the site settings and file uploads.
     */
    public function update(Request $request): RedirectResponse
    {
        $this->ensureSuperAdmin($request);

        $validated = $request->validate([
            // General
            'site_name' => 'required|string|max:255',
            'site_name_en' => 'nullable|string|max:255',
            'site_logo' => 'nullable|file|mimes:jpeg,png,jpg,svg,webp|max:5120',
            'site_favicon' => 'nullable|file|mimes:ico,png,svg|max:2048',
            'site_apple_icon' => 'nullable|file|mimes:png,jpg,jpeg|max:2048',
            'app_apk_file' => [
                'nullable',
                'file',
                'max:102400',
                function ($attribute, $value, $fail) {
                    if ($value instanceof \Illuminate\Http\UploadedFile) {
                        $ext = strtolower($value->getClientOriginalExtension());
                        if ($ext !== 'apk') {
                            $fail('يجب أن يكون الملف المرفوع بصيغة تطبيق أندرويد (.apk).');
                        }
                    }
                },
            ],

            // SEO & Head Meta
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:1000',
            'meta_keywords' => 'nullable|string|max:500',
            'meta_author' => 'nullable|string|max:255',
            'meta_robots' => 'nullable|string|max:100',
            'canonical_url' => 'nullable|url|max:255',

            // Social & Open Graph
            'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string|max:1000',
            'og_image' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:5120',
            'twitter_card' => 'nullable|string|in:summary,summary_large_image',
            'twitter_site' => 'nullable|string|max:100',

            // Scripts & Analytics
            'google_site_verification' => 'nullable|string|max:255',
            'google_analytics_id' => 'nullable|string|max:50',
            'custom_head_scripts' => 'nullable|string',
            'custom_footer_scripts' => 'nullable|string',

            // Contact & Support
            'support_hotline' => 'nullable|string|max:50',
            'support_email' => 'nullable|email|max:255',
            'support_address' => 'nullable|string|max:500',
            'social_facebook' => 'nullable|url|max:255',
            'social_twitter' => 'nullable|url|max:255',
            'social_telegram' => 'nullable|url|max:255',
        ]);

        $this->settingService->updateSettings(
            $validated,
            $request->allFiles(),
            $request->user()
        );

        return back()->with('success', 'تم حفظ وتحديث إعدادات الموقع وهوية وسوم Head بنجاح.');
    }

    /**
     * Handle asynchronous APK upload with real-time progress tracking and validation up to 100MB.
     */
    public function uploadApk(Request $request): JsonResponse
    {
        $this->ensureSuperAdmin($request);

        // Increase execution time and memory limits for processing uploads up to 100MB
        @ini_set('max_execution_time', '600');
        @ini_set('memory_limit', '512M');
        if (function_exists('set_time_limit')) {
            @set_time_limit(600);
        }

        $request->validate([
            'app_apk_file' => [
                'required',
                'file',
                'max:102400', // 100MB in KB
                function ($attribute, $value, $fail) {
                    if ($value instanceof \Illuminate\Http\UploadedFile) {
                        $ext = strtolower($value->getClientOriginalExtension());
                        if ($ext !== 'apk') {
                            $fail('يجب أن يكون الملف المرفوع بصيغة تطبيق أندرويد (.apk) فقط.');
                        }
                    }
                },
            ],
        ], [
            'app_apk_file.required' => 'يرجى اختيار ملف APK لرفعه.',
            'app_apk_file.file' => 'الملف المرفوع غير صالح.',
            'app_apk_file.max' => 'حجم ملف التطبيق يتجاوز الحد الأقصى المسموح به (100 ميجابايت).',
        ]);

        $file = $request->file('app_apk_file');

        // Delete old file if present
        $oldPath = Setting::get('app_apk_file');
        if ($oldPath && is_string($oldPath)) {
            $this->settingService->deleteSettingFile('app_apk_file', null);
        }

        // Ensure any previous بادر.apk file is cleared from storage
        $disk = config('balagh.storage.disk', 'public');
        if (\Illuminate\Support\Facades\Storage::disk($disk)->exists('settings/بادر.apk')) {
            \Illuminate\Support\Facades\Storage::disk($disk)->delete('settings/بادر.apk');
        }

        // Upload new file to public settings directory with exact name: بادر.apk
        $upload = app(\App\Services\FileUploadService::class)->upload($file, 'settings', null, 'بادر.apk');
        Setting::set('app_apk_file', $upload['file_path'], 'general');
        Setting::clearCache();

        // Audit log
        \App\Models\ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'upload_apk_file',
            'model_type' => Setting::class,
            'model_id' => 0,
            'changes' => [
                'file_name' => $upload['file_name'],
                'file_size' => $upload['file_size'],
                'file_path' => $upload['file_path'],
            ],
            'ip_address' => $request->ip(),
        ]);

        $fileSizeMb = round($upload['file_size'] / (1024 * 1024), 2);

        return response()->json([
            'success' => true,
            'message' => 'تم رفع وتثبيت تطبيق الأندرويد بنجاح.',
            'file_url' => asset('storage/' . ltrim($upload['file_path'], '/')),
            'file_name' => $upload['file_name'],
            'file_size' => $fileSizeMb . ' MB',
        ]);
    }

    /**
     * Delete the uploaded APK file.
     */
    public function deleteApk(Request $request): JsonResponse|RedirectResponse
    {
        $this->ensureSuperAdmin($request);

        $this->settingService->deleteSettingFile('app_apk_file', $request->user());

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تم حذف ملف تطبيق الأندرويد (APK) بنجاح.',
            ]);
        }

        return back()->with('success', 'تم حذف ملف تطبيق الأندرويد (APK) بنجاح.');
    }

    /**
     * Enforce strict Super Admin authorization.
     */
    protected function ensureSuperAdmin(Request $request): void
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole('Super Admin')) {
            abort(403, 'عذراً، هذا المسار مخصص للإدارة العامة للنظام فقط.');
        }

        if (! $user->is_active) {
            abort(403, 'حسابك معطل حالياً. يرجى التواصل مع الدعم الفني.');
        }
    }
}
