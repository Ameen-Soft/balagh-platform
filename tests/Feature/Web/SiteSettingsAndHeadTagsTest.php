<?php

namespace Tests\Feature\Web;

use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SiteSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteSettingsAndHeadTagsTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $ministryAdmin;

    protected User $citizen;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(SiteSettingsSeeder::class);

        $superAdminRole = Role::where('name', 'Super Admin')->firstOrFail();
        $ministryAdminRole = Role::where('name', 'Ministry Admin')->firstOrFail();
        $citizenRole = Role::where('name', 'Citizen')->firstOrFail();

        $this->superAdmin = User::factory()->create([
            'name' => 'المدير العام',
            'email' => 'superadmin@balagh.gov.ye',
            'is_active' => true,
        ]);
        $this->superAdmin->roles()->sync([$superAdminRole->id]);

        $this->ministryAdmin = User::factory()->create([
            'name' => 'مشرف الوزارة',
            'email' => 'ministry@balagh.gov.ye',
            'is_active' => true,
        ]);
        $this->ministryAdmin->roles()->sync([$ministryAdminRole->id]);

        $this->citizen = User::factory()->create([
            'name' => 'المواطن',
            'email' => 'citizen@balagh.gov.ye',
            'is_active' => true,
        ]);
        $this->citizen->roles()->sync([$citizenRole->id]);
    }

    public function test_unauthenticated_user_cannot_access_settings_routes(): void
    {
        $this->get(route('admin.settings.index'))
            ->assertRedirect(route('login'));

        $this->post(route('admin.settings.update'), ['site_name' => 'اختراق'])
            ->assertRedirect(route('login'));
    }

    public function test_non_super_admin_users_receive_forbidden_403(): void
    {
        // Citizen
        $this->actingAs($this->citizen)
            ->get(route('admin.settings.index'))
            ->assertStatus(403);

        $this->actingAs($this->citizen)
            ->post(route('admin.settings.update'), ['site_name' => 'محاولة مواطن'])
            ->assertStatus(403);

        // Ministry Admin
        $this->actingAs($this->ministryAdmin)
            ->get(route('admin.settings.index'))
            ->assertStatus(403);

        $this->actingAs($this->ministryAdmin)
            ->post(route('admin.settings.update'), ['site_name' => 'محاولة مشرف وزارة'])
            ->assertStatus(403);
    }

    public function test_super_admin_can_view_settings_page(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('admin.settings.index'));

        $response->assertStatus(200)
            ->assertSee('إدارة إعدادات المنصة وهوية وسوم Head')
            ->assertSee('الهوية والشعارات والأيقونات')
            ->assertSee('وسوم السيو')
            ->assertSee('أكواد التحقق');
    }

    public function test_super_admin_can_update_settings_and_logs_activity(): void
    {
        $payload = [
            'site_name' => 'منصة بادر الوطنية المحدثة',
            'site_name_en' => 'Bader Updated Platform',
            'meta_title' => 'منصة بادر الرسمية باليمن',
            'meta_description' => 'وصف اختباري مخصص لمنصة بادر للتأكد من حفظ وتحديث البيانات الوصفية.',
            'meta_keywords' => 'بادر, بلاغات, تجربة',
            'meta_author' => 'رئاسة الوزراء - الجمهورية اليمنية',
            'meta_robots' => 'index, follow',
            'canonical_url' => 'https://balagh.gov.ye',
            'og_title' => 'مشاركة بادر في فيسبوك',
            'og_description' => 'وصف مشاركة مخصص',
            'twitter_card' => 'summary_large_image',
            'twitter_site' => '@BaderYe',
            'google_site_verification' => 'google-verify-sample-12345',
            'google_analytics_id' => 'G-ABC123DEF4',
            'custom_head_scripts' => '<!-- Custom Head Tracking Code -->',
            'custom_footer_scripts' => '<!-- Custom Footer Code -->',
            'support_hotline' => '999111',
            'support_email' => 'contact@balagh.gov.ye',
            'support_address' => 'صنعاء، التحرير',
        ];

        $response = $this->actingAs($this->superAdmin)
            ->from(route('admin.settings.index'))
            ->post(route('admin.settings.update'), $payload);

        $response->assertRedirect(route('admin.settings.index'))
            ->assertSessionHas('success');

        // Verify Database
        $this->assertDatabaseHas('settings', [
            'key' => 'site_name',
            'value' => 'منصة بادر الوطنية المحدثة',
        ]);
        $this->assertDatabaseHas('settings', [
            'key' => 'support_hotline',
            'value' => '999111',
        ]);

        // Verify Setting helper
        $this->assertSame('منصة بادر الوطنية المحدثة', Setting::get('site_name'));
        $this->assertSame('999111', Setting::get('support_hotline'));

        // Verify Activity Log
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->superAdmin->id,
            'action' => 'update_site_settings',
        ]);
    }

    public function test_super_admin_can_upload_branding_files_and_favicons(): void
    {
        Storage::fake('public');

        $logoFile = UploadedFile::fake()->create('new_logo.png', 50, 'image/png');
        $faviconFile = UploadedFile::fake()->create('favicon.png', 10, 'image/png');
        $apkFile = UploadedFile::fake()->create('bader_app.apk', 500, 'application/vnd.android.package-archive');

        $response = $this->actingAs($this->superAdmin)
            ->from(route('admin.settings.index'))
            ->post(route('admin.settings.update'), [
                'site_name' => 'بــــادر',
                'site_logo' => $logoFile,
                'site_favicon' => $faviconFile,
                'app_apk_file' => $apkFile,
            ]);

        $response->assertRedirect(route('admin.settings.index'))
            ->assertSessionHas('success');

        $savedLogo = Setting::get('site_logo');
        $savedFavicon = Setting::get('site_favicon');
        $savedApk = Setting::get('app_apk_file');

        $this->assertNotNull($savedLogo);
        $this->assertNotNull($savedFavicon);
        $this->assertNotNull($savedApk);

        Storage::disk('public')->assertExists($savedLogo);
        Storage::disk('public')->assertExists($savedFavicon);
        Storage::disk('public')->assertExists($savedApk);
    }

    public function test_head_component_renders_customized_settings_on_pages(): void
    {
        Setting::set('site_name', 'منصة بادر للاختبار');
        Setting::set('app_apk_file', 'settings/test_app.apk');
        Setting::set('google_site_verification', 'test-unique-g-verify');
        Setting::set('google_analytics_id', 'G-TEST99999');

        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertSee('<meta name="google-site-verification" content="test-unique-g-verify">', false)
            ->assertSee('G-TEST99999')
            ->assertSee('منصة بادر للاختبار')
            ->assertSee('تنزيل التطبيق (APK)');
    }

    public function test_public_api_endpoint_returns_filtered_settings_cleanly(): void
    {
        Setting::set('site_name', 'منصة بادر العامة');
        Setting::set('support_hotline', '8000999');
        Setting::set('app_apk_file', 'settings/test_app.apk');

        $response = $this->getJson('/api/v1/settings');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'site_name' => 'منصة بادر العامة',
                    'support_hotline' => '8000999',
                ],
            ]);

        $this->assertStringContainsString('settings/test_app.apk', $response->json('data.app_apk_url'));

        // Secret or raw script settings must not be leaked in public API
        $response->assertJsonMissing([
            'custom_head_scripts',
            'custom_footer_scripts',
        ]);
    }

    public function test_super_admin_can_delete_uploaded_apk_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('settings/my_app.apk', 'sample apk binary content');
        Setting::set('app_apk_file', 'settings/my_app.apk');

        $this->assertNotNull(Setting::get('app_apk_file'));
        Storage::disk('public')->assertExists('settings/my_app.apk');

        $response = $this->actingAs($this->superAdmin)
            ->delete(route('admin.settings.delete_apk'));

        $response->assertRedirect()
            ->assertSessionHas('success');

        $this->assertNull(Setting::get('app_apk_file'));
        Storage::disk('public')->assertMissing('settings/my_app.apk');

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->superAdmin->id,
            'action' => 'delete_site_setting_file',
        ]);
    }
}
