<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class SiteSettingApiController extends Controller
{
    /**
     * Get public site settings for mobile application and external consumers.
     */
    public function index(): JsonResponse
    {
        $settings = Setting::getAllCached();

        $logoPath = $settings->get('site_logo');
        $faviconPath = $settings->get('site_favicon');
        $apkPath = $settings->get('app_apk_file');

        $data = [
            'site_name' => $settings->get('site_name', 'بــــادر'),
            'site_name_en' => $settings->get('site_name_en', 'Bader Platform'),
            'site_logo_url' => $logoPath ? asset('storage/'.ltrim($logoPath, '/')) : null,
            'site_favicon_url' => $faviconPath ? asset('storage/'.ltrim($faviconPath, '/')) : null,
            'app_apk_url' => $apkPath ? asset('storage/'.ltrim($apkPath, '/')) : null,
            'support_hotline' => $settings->get('support_hotline', '8000888'),
            'support_email' => $settings->get('support_email', 'support@balagh.gov.ye'),
            'support_address' => $settings->get('support_address', 'صنعاء، الجمهورية اليمنية'),
            'social_links' => [
                'facebook' => $settings->get('social_facebook'),
                'twitter' => $settings->get('social_twitter'),
                'telegram' => $settings->get('social_telegram'),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
