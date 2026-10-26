<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General & Identity
            ['key' => 'site_name', 'value' => 'بــــادر', 'group' => 'general'],
            ['key' => 'site_name_en', 'value' => 'Bader Platform', 'group' => 'general'],
            ['key' => 'site_logo', 'value' => null, 'group' => 'general'],
            ['key' => 'site_favicon', 'value' => null, 'group' => 'general'],
            ['key' => 'site_apple_icon', 'value' => null, 'group' => 'general'],

            // SEO & Head Meta
            ['key' => 'meta_title', 'value' => 'منصة بادر - البوابة الوطنية الموحدة للبلاغات والشكاوى التنموية', 'group' => 'seo'],
            ['key' => 'meta_description', 'value' => 'منصة بادر - المنظومة الوطنية الذكية لربط المواطن بالجهات الحكومية، رصد الشكاوى وإشراك المجتمع في الرقابة ومتابعة المشاريع.', 'group' => 'seo'],
            ['key' => 'meta_keywords', 'value' => 'بادر, بلاغات, شكاوى, مشاريع تنموية, الجمهورية اليمنية, رقابة مجتمعية', 'group' => 'seo'],
            ['key' => 'meta_author', 'value' => 'الجمهورية اليمنية - منصة بادر', 'group' => 'seo'],
            ['key' => 'meta_robots', 'value' => 'index, follow', 'group' => 'seo'],
            ['key' => 'canonical_url', 'value' => null, 'group' => 'seo'],

            // Social & Open Graph
            ['key' => 'og_title', 'value' => 'منصة بادر - البوابة الوطنية الموحدة للبلاغات والشكاوى التنموية', 'group' => 'social'],
            ['key' => 'og_description', 'value' => 'منظومة وطنية ذكية تربط المواطن بالجهات الحكومية لمعالجة الشكاوى ومتابعة المشاريع التنموية بكل شفافية.', 'group' => 'social'],
            ['key' => 'og_image', 'value' => null, 'group' => 'social'],
            ['key' => 'twitter_card', 'value' => 'summary_large_image', 'group' => 'social'],
            ['key' => 'twitter_site', 'value' => '@BaderPlatform', 'group' => 'social'],

            // Scripts & Tracking
            ['key' => 'google_site_verification', 'value' => null, 'group' => 'scripts'],
            ['key' => 'google_analytics_id', 'value' => null, 'group' => 'scripts'],
            ['key' => 'custom_head_scripts', 'value' => null, 'group' => 'scripts'],
            ['key' => 'custom_footer_scripts', 'value' => null, 'group' => 'scripts'],

            // Contact & Support
            ['key' => 'support_hotline', 'value' => '8000888', 'group' => 'contact'],
            ['key' => 'support_email', 'value' => 'support@balagh.gov.ye', 'group' => 'contact'],
            ['key' => 'support_address', 'value' => 'صنعاء، الجمهورية اليمنية', 'group' => 'contact'],
            ['key' => 'social_facebook', 'value' => 'https://facebook.com', 'group' => 'contact'],
            ['key' => 'social_twitter', 'value' => 'https://twitter.com', 'group' => 'contact'],
            ['key' => 'social_telegram', 'value' => 'https://t.me', 'group' => 'contact'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        Setting::clearCache();
    }
}
