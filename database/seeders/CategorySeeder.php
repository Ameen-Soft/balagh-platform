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
            ['name' => 'البنية التحتية وشبكة الطرق', 'department_id' => $roadsDept->id],
            [
                'parent_id' => null,
                'description' => 'كافة البلاغات المرتبطة بشبكات الطرق والجسور والمرافق العامة.',
                'level' => 1,
            ]
        );

        Category::firstOrCreate(
            ['name' => 'حفر وتشققات إسفلتية خطرة', 'department_id' => $roadsDept->id],
            [
                'parent_id' => $rootRoads->id,
                'description' => 'وجود حفر في الطريق تعيق حركة السير أو تشكل خطراً على المركبات.',
                'level' => 2,
            ]
        );

        Category::firstOrCreate(
            ['name' => 'انهيار أو تصدع في الجسور', 'department_id' => $roadsDept->id],
            [
                'parent_id' => $rootRoads->id,
                'description' => 'تصدعات إنشائية في جسور المشاة أو المركبات تستوجب التدخل الفوري.',
                'level' => 2,
            ]
        );

        Category::firstOrCreate(
            ['name' => 'تضرر اللوحات الإرشادية والمرافق', 'department_id' => $facilitiesDept->id],
            [
                'parent_id' => $rootRoads->id,
                'description' => 'تلف أو سقوط الإشارات المرورية واللوحات التعريفية الحكومية.',
                'level' => 2,
            ]
        );

        // 2. Root: خدمات الكهرباء والإنارة
        $rootElectricity = Category::firstOrCreate(
            ['name' => 'خدمات الكهرباء والطاقة', 'department_id' => $gridDept->id],
            [
                'parent_id' => null,
                'description' => 'بلاغات شبكة الكهرباء العامة ومخاطر الكابلات وأعمدة الإنارة.',
                'level' => 1,
            ]
        );

        Category::firstOrCreate(
            ['name' => 'أعمدة إنارة معطلة في الشوارع', 'department_id' => $lightingDept->id],
            [
                'parent_id' => $rootElectricity->id,
                'description' => 'انطفاء أو كسر أعمدة الإنارة العامة في الأحياء والشوارع الرئيسية.',
                'level' => 2,
            ]
        );

        Category::firstOrCreate(
            ['name' => 'أسلاك كهربائية مكشوفة أو محول خطر', 'department_id' => $gridDept->id],
            [
                'parent_id' => $rootElectricity->id,
                'description' => 'تماس كهربائي أو تدلي كابلات الضغط العالي قرب التجمعات السكنية.',
                'level' => 2,
            ]
        );

        // 3. Root: المياه والصرف الصحي
        $rootWater = Category::firstOrCreate(
            ['name' => 'المياه والإصحاح البيئي', 'department_id' => $waterDept->id],
            [
                'parent_id' => null,
                'description' => 'إمدادات المياه وصيانة شبكات الصرف الصحي وقنوات السيول.',
                'level' => 1,
            ]
        );

        Category::firstOrCreate(
            ['name' => 'كسر أو تسرب في خطوط المياه الرئيسية', 'department_id' => $waterDept->id],
            [
                'parent_id' => $rootWater->id,
                'description' => 'هدر كميات كبيرة من مياه الشرب بسبب كسور في الشبكة العامة.',
                'level' => 2,
            ]
        );

        Category::firstOrCreate(
            ['name' => 'طفح مياه الصرف الصحي (المجاري)', 'department_id' => $sewageDept->id],
            [
                'parent_id' => $rootWater->id,
                'description' => 'انسداد غرف التفتيش وطفح مياه الصرف الصحي في الشوارع العامة.',
                'level' => 2,
            ]
        );
    }
}
