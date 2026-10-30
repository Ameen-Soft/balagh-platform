<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Department;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch departments
        $roadsDept = Department::where('name', 'إدارة الطرق والجسور')->first();
        $facilitiesDept = Department::where('name', 'إدارة المرافق والمباني الحكومية')->first();
        $lightingDept = Department::where('name', 'إدارة إنارة الشوارع والطاقة المتجددة')->first();
        $gridDept = Department::where('name', 'إدارة صيانة الشبكة الكهربائية')->first();
        $waterDept = Department::where('name', 'إدارة شبكات الإمداد المائي')->first();
        $sewageDept = Department::where('name', 'إدارة الصرف الصحي وتصريف السيول')->first();

        // 1. Root: البنية التحتية والطرق
        $rootRoads = Category::firstOrCreate(
            ['name' => 'البنية التحتية وشبكة الطرق', 'ministry_id' => $roadsDept->ministry_id, 'parent_id' => null],
            [
                'department_id' => null,
                'description' => 'كافة البلاغات المرتبطة بشبكات الطرق والجسور والمرافق العامة.',
                'level' => 1,
                'is_active' => true,
            ]
        );

        Category::firstOrCreate(
            ['name' => 'حفر وتشققات إسفلتية خطرة', 'parent_id' => $rootRoads->id],
            [
                'ministry_id' => $roadsDept->ministry_id,
                'department_id' => $roadsDept->id,
                'description' => 'وجود حفر في الطريق تعيق حركة السير أو تشكل خطراً على المركبات.',
                'level' => 2,
                'is_active' => true,
            ]
        );

        Category::firstOrCreate(
            ['name' => 'انهيار أو تصدع في الجسور', 'parent_id' => $rootRoads->id],
            [
                'ministry_id' => $roadsDept->ministry_id,
                'department_id' => $roadsDept->id,
                'description' => 'تصدعات إنشائية في جسور المشاة أو المركبات تستوجب التدخل الفوري.',
                'level' => 2,
                'is_active' => true,
            ]
        );

        Category::firstOrCreate(
            ['name' => 'تضرر اللوحات الإرشادية والمرافق', 'parent_id' => $rootRoads->id],
            [
                'ministry_id' => $facilitiesDept->ministry_id,
                'department_id' => $facilitiesDept->id,
                'description' => 'تلف أو سقوط الإشارات المرورية واللوحات التعريفية الحكومية.',
                'level' => 2,
                'is_active' => true,
            ]
        );

        // 2. Root: خدمات الكهرباء والإنارة
        $rootElectricity = Category::firstOrCreate(
            ['name' => 'خدمات الكهرباء والطاقة', 'ministry_id' => $gridDept->ministry_id, 'parent_id' => null],
            [
                'department_id' => null,
                'description' => 'بلاغات شبكة الكهرباء العامة ومخاطر الكابلات وأعمدة الإنارة.',
                'level' => 1,
                'is_active' => true,
            ]
        );

        Category::firstOrCreate(
            ['name' => 'أعمدة إنارة معطلة في الشوارع', 'parent_id' => $rootElectricity->id],
            [
                'ministry_id' => $lightingDept->ministry_id,
                'department_id' => $lightingDept->id,
                'description' => 'انطفاء أو كسر أعمدة الإنارة العامة في الأحياء والشوارع الرئيسية.',
                'level' => 2,
                'is_active' => true,
            ]
        );

        Category::firstOrCreate(
            ['name' => 'أسلاك كهربائية مكشوفة أو محول خطر', 'parent_id' => $rootElectricity->id],
            [
                'ministry_id' => $gridDept->ministry_id,
                'department_id' => $gridDept->id,
                'description' => 'تماس كهربائي أو تدلي كابلات الضغط العالي قرب التجمعات السكنية.',
                'level' => 2,
                'is_active' => true,
            ]
        );

        // 3. Root: المياه والصرف الصحي
        $rootWater = Category::firstOrCreate(
            ['name' => 'المياه والإصحاح البيئي', 'ministry_id' => $waterDept->ministry_id, 'parent_id' => null],
            [
                'department_id' => null,
                'description' => 'إمدادات المياه وصيانة شبكات الصرف الصحي وقنوات السيول.',
                'level' => 1,
                'is_active' => true,
            ]
        );

        Category::firstOrCreate(
            ['name' => 'كسر أو تسرب في خطوط المياه الرئيسية', 'parent_id' => $rootWater->id],
            [
                'ministry_id' => $waterDept->ministry_id,
                'department_id' => $waterDept->id,
                'description' => 'هدر كميات كبيرة من مياه الشرب بسبب كسور في الشبكة العامة.',
                'level' => 2,
                'is_active' => true,
            ]
        );

        Category::firstOrCreate(
            ['name' => 'طفح مياه الصرف الصحي (المجاري)', 'parent_id' => $rootWater->id],
            [
                'ministry_id' => $sewageDept->ministry_id,
                'department_id' => $sewageDept->id,
                'description' => 'انسداد غرف التفتيش وطفح مياه الصرف الصحي في الشوارع العامة.',
                'level' => 2,
                'is_active' => true,
            ]
        );
    }
}
