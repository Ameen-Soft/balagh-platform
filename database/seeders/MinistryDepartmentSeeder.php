<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Ministry;
use Illuminate\Database\Seeder;

class MinistryDepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ministries = [
            [
                'name' => 'وزارة الأشغال العامة والطرق',
                'code' => 'MOPW',
                'logo' => 'logos/mopw.png',
                'contact_email' => 'contact@mopw.gov.ye',
                'is_active' => true,
                'departments' => [
                    [
                        'name' => 'إدارة الطرق والجسور',
                        'description' => 'صيانة شبكات الطرق الرئيسية والفرعية وإصلاح التصدعات والجسور.',
                        'is_active' => true,
                    ],
                    [
                        'name' => 'إدارة المرافق والمباني الحكومية',
                        'description' => 'متابعة سلامة المنشآت الخدمية والمرافق العامة التابعة للدولة.',
                        'is_active' => true,
                    ],
                ],
            ],
            [
                'name' => 'وزارة الكهرباء والطاقة',
                'code' => 'MOEE',
                'logo' => 'logos/moee.png',
                'contact_email' => 'info@moee.gov.ye',
                'is_active' => true,
                'departments' => [
                    [
                        'name' => 'إدارة صيانة الشبكة الكهربائية',
                        'description' => 'معالجة انقطاعات التيار، صيانة المحولات والأسلاك الكهربائية المكشوفة.',
                        'is_active' => true,
                    ],
                    [
                        'name' => 'إدارة إنارة الشوارع والطاقة المتجددة',
                        'description' => 'صيانة واستبدال أعمدة الإنارة العامة وتشغيل مشاريع الطاقة الشمسية.',
                        'is_active' => true,
                    ],
                ],
            ],
            [
                'name' => 'وزارة المياه والبيئة',
                'code' => 'MOWE',
                'logo' => 'logos/mowe.png',
                'contact_email' => 'support@mowe.gov.ye',
                'is_active' => true,
                'departments' => [
                    [
                        'name' => 'إدارة شبكات الإمداد المائي',
                        'description' => 'إصلاح كسور خطوط المياه الرئيسية وضمان استمرارية التوزيع.',
                        'is_active' => true,
                    ],
                    [
                        'name' => 'إدارة الصرف الصحي وتصريف السيول',
                        'description' => 'معالجة طفح مياه الصرف الصحي وصيانة قنوات تصريف مياه الأمطار.',
                        'is_active' => true,
                    ],
                ],
            ],
        ];

        foreach ($ministries as $ministryData) {
            $departments = $ministryData['departments'];
            unset($ministryData['departments']);

            $ministry = Ministry::firstOrCreate(
                ['code' => $ministryData['code']],
                $ministryData
            );

            foreach ($departments as $deptData) {
                Department::firstOrCreate(
                    [
                        'ministry_id' => $ministry->id,
                        'name' => $deptData['name'],
                    ],
                    $deptData
                );
            }
        }
    }
}
