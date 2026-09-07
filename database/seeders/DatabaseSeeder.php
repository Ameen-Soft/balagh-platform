<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database in strict linear order.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            MinistryDepartmentSeeder::class,
            CategorySeeder::class,
            UserSeeder::class,
            ComplaintSeeder::class,
            ProjectSeeder::class,
        ]);

        // General System Settings (Reference Data)
        $settings = [
            ['key' => 'site_name', 'value' => 'بوابة بلاغ الوطنية الموحدة', 'group' => 'general'],
            ['key' => 'support_hotline', 'value' => '8000888', 'group' => 'general'],
            ['key' => 'max_attachment_size_mb', 'value' => '15', 'group' => 'media'],
            ['key' => 'enable_gps_enforcement', 'value' => 'true', 'group' => 'security'],
            ['key' => 'crowdfunding_simulation_active', 'value' => 'true', 'group' => 'projects'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
