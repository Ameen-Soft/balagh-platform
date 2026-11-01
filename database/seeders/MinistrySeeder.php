<?php

namespace Database\Seeders;

use App\Models\Ministry;
use Illuminate\Database\Seeder;

class MinistrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seed the 20 approved ministries for Bader Platform.
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
            ],
            [
                'name' => 'وزارة الكهرباء والطاقة',
                'code' => 'MOEE',
                'logo' => 'logos/moee.png',
                'contact_email' => 'info@moee.gov.ye',
                'is_active' => true,
            ],
            [
                'name' => 'وزارة المياه والبيئة',
                'code' => 'MOWE',
                'logo' => 'logos/mowe.png',
                'contact_email' => 'support@mowe.gov.ye',
                'is_active' => true,
            ],
            [
                'name' => 'وزارة الاتصالات وتقنية المعلومات',
                'code' => 'MOITT',
                'logo' => 'logos/moitt.png',
                'contact_email' => 'info@moitt.gov.ye',
                'is_active' => true,
            ],
            [
                'name' => 'وزارة النقل',
                'code' => 'MOT',
                'logo' => 'logos/mot.png',
                'contact_email' => 'info@mot.gov.ye',
                'is_active' => true,
            ],
            [
                'name' => 'وزارة الإدارة المحلية',
                'code' => 'MOLA',
                'logo' => 'logos/mola.png',
                'contact_email' => 'info@mola.gov.ye',
                'is_active' => true,
            ],
            [
                'name' => 'وزارة التخطيط والتعاون الدولي',
                'code' => 'MOPIC',
                'logo' => 'logos/mopic.png',
                'contact_email' => 'info@mopic.gov.ye',
                'is_active' => true,
            ],
            [
                'name' => 'وزارة الصحة العامة والسكان',
                'code' => 'MOPHP',
                'logo' => 'logos/mophp.png',
                'contact_email' => 'info@mophp.gov.ye',
                'is_active' => true,
            ],
            [
                'name' => 'وزارة الصناعة والتجارة',
                'code' => 'MOIC',
                'logo' => 'logos/moic.png',
                'contact_email' => 'info@moic.gov.ye',
                'is_active' => true,
            ],
            [
                'name' => 'وزارة النفط والمعادن',
                'code' => 'MOPM',
                'logo' => 'logos/mopm.png',
                'contact_email' => 'info@mopm.gov.ye',
                'is_active' => true,
            ],
            [
                'name' => 'وزارة الزراعة والري',
                'code' => 'MAI',
                'logo' => 'logos/mai.png',
                'contact_email' => 'info@mai.gov.ye',
                'is_active' => true,
            ],
            [
                'name' => 'وزارة الثروة السمكية',
                'code' => 'MOFW',
                'logo' => 'logos/mofw.png',
                'contact_email' => 'info@mofw.gov.ye',
                'is_active' => true,
            ],
            [
                'name' => 'وزارة التربية والتعليم',
                'code' => 'MOE',
                'logo' => 'logos/moe.png',
                'contact_email' => 'info@moe.gov.ye',
                'is_active' => true,
            ],
            [
                'name' => 'وزارة التعليم الفني والتدريب المهني',
                'code' => 'MOTEVT',
                'logo' => 'logos/motevt.png',
                'contact_email' => 'info@motevt.gov.ye',
                'is_active' => true,
            ],
            [
                'name' => 'وزارة الشئون الاجتماعية والعمل',
                'code' => 'MOSAL',
                'logo' => 'logos/mosal.png',
                'contact_email' => 'info@mosal.gov.ye',
                'is_active' => true,
            ],
            [
                'name' => 'وزارة الأوقاف والإرشاد',
                'code' => 'MOAE',
                'logo' => 'logos/moae.png',
                'contact_email' => 'info@moae.gov.ye',
                'is_active' => true,
            ],
            [
                'name' => 'وزارة الشباب والرياضة',
                'code' => 'MOYS',
                'logo' => 'logos/moys.png',
                'contact_email' => 'info@moys.gov.ye',
                'is_active' => true,
            ],
            [
                'name' => 'وزارة السياحة',
                'code' => 'MOTOUR',
                'logo' => 'logos/motour.png',
                'contact_email' => 'info@motour.gov.ye',
                'is_active' => true,
            ],
            [
                'name' => 'وزارة الثقافة',
                'code' => 'MOC',
                'logo' => 'logos/moc.png',
                'contact_email' => 'info@moc.gov.ye',
                'is_active' => true,
            ],
            [
                'name' => 'وزارة الداخلية',
                'code' => 'MOI',
                'logo' => 'logos/moi.png',
                'contact_email' => 'info@moi.gov.ye',
                'is_active' => true,
            ],
        ];

        foreach ($ministries as $ministryData) {
            Ministry::firstOrCreate(
                ['code' => $ministryData['code']],
                $ministryData
            );
        }
    }
}

