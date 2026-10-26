<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class SettingService
{
    public function __construct(
        protected FileUploadService $fileUploadService
    ) {}

    /**
     * Map of settings to their logical groups.
     *
     * @var array<string, string>
     */
    protected array $keyGroupMap = [
        // General & Identity
        'site_name' => 'general',
        'site_name_en' => 'general',
        'site_logo' => 'general',
        'site_favicon' => 'general',
        'site_apple_icon' => 'general',
        'app_apk_file' => 'general',

        // SEO & Head Meta
        'meta_title' => 'seo',
        'meta_description' => 'seo',
        'meta_keywords' => 'seo',
        'meta_author' => 'seo',
        'meta_robots' => 'seo',
        'canonical_url' => 'seo',

        // Social & Open Graph
        'og_title' => 'social',
        'og_description' => 'social',
        'og_image' => 'social',
        'twitter_card' => 'social',
        'twitter_site' => 'social',

        // Scripts & Tracking
        'google_site_verification' => 'scripts',
        'google_analytics_id' => 'scripts',
        'custom_head_scripts' => 'scripts',
        'custom_footer_scripts' => 'scripts',

        // Contact & Support
        'support_hotline' => 'contact',
        'support_email' => 'contact',
        'support_address' => 'contact',
        'social_facebook' => 'contact',
        'social_twitter' => 'contact',
        'social_telegram' => 'contact',
    ];

    /**
     * File uploadable settings keys.
     *
     * @var array<string>
     */
    protected array $fileKeys = [
        'site_logo',
        'site_favicon',
        'site_apple_icon',
        'og_image',
        'app_apk_file',
    ];

    /**
     * Retrieve all settings grouped by group name.
     */
    public function getGroupedSettings(): array
    {
        $all = Setting::all();
        $grouped = [];

        foreach ($all as $setting) {
            $grouped[$setting->group][$setting->key] = $setting->value;
        }

        return $grouped;
    }

    /**
     * Update settings including text fields and uploaded files.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, UploadedFile>  $files
     */
    public function updateSettings(array $data, array $files = [], ?User $user = null): void
    {
        $modifiedKeys = [];

        // 1. Process File Uploads
        foreach ($this->fileKeys as $fileKey) {
            if (isset($files[$fileKey]) && $files[$fileKey] instanceof UploadedFile) {
                // Delete old file if present
                $oldPath = Setting::get($fileKey);
                if ($oldPath && is_string($oldPath)) {
                    $this->fileUploadService->delete($oldPath);
                }

                // Upload new file to public settings directory
                $upload = $this->fileUploadService->upload($files[$fileKey], 'settings');
                $group = $this->keyGroupMap[$fileKey] ?? 'general';

                Setting::set($fileKey, $upload['file_path'], $group);
                $modifiedKeys[] = $fileKey;
            }
        }

        // 2. Process Text/String/Code Settings
        foreach ($data as $key => $value) {
            if (in_array($key, $this->fileKeys, true)) {
                continue;
            }

            if (array_key_exists($key, $this->keyGroupMap)) {
                $group = $this->keyGroupMap[$key];
                $val = ($value === null || $value === '') ? null : (string) $value;

                Setting::set($key, $val, $group);
                $modifiedKeys[] = $key;
            }
        }

        // 3. Invalidate Cache
        Setting::clearCache();

        // 4. Log Administrative Audit Trail
        if ($user) {
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'update_site_settings',
                'model_type' => Setting::class,
                'model_id' => 0,
                'changes' => [
                    'updated_keys' => $modifiedKeys,
                ],
                'ip_address' => request()->ip(),
            ]);
        }
    }

    /**
     * Delete an uploaded setting file and clear its setting value.
     */
    public function deleteSettingFile(string $key, ?User $user = null): void
    {
        $oldPath = Setting::get($key);
        if ($oldPath && is_string($oldPath)) {
            $this->fileUploadService->delete($oldPath);
        }

        $group = $this->keyGroupMap[$key] ?? 'general';
        Setting::set($key, null, $group);
        Setting::clearCache();

        if ($user) {
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'delete_site_setting_file',
                'model_type' => Setting::class,
                'model_id' => 0,
                'changes' => [
                    'deleted_key' => $key,
                ],
                'ip_address' => request()->ip(),
            ]);
        }
    }
}
