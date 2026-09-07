<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Project;
use App\Models\ProjectContribution;
use App\Models\ProjectPhase;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Controlled seed strategy: only seed if no projects exist
        if (Project::count() > 0) {
            return;
        }

        $admin = User::where('email', 'admin@example.test')->first();
        $citizen = User::where('email', 'citizen@example.test')->first();

        $roadsDept = Department::where('name', 'إدارة الطرق والجسور')->first();
        $lightingDept = Department::where('name', 'إدارة إنارة الشوارع والطاقة المتجددة')->first();

        // 1. Project 1: Bridges rehabilitation
        $p1 = Project::create([
            'title' => 'مشروع صيانة وتأهيل جسر الصداقة والتقاطعات المجاورة',
            'description' => 'إعادة سفلتة وتدعيم فواصل التمدد لجسر الصداقة وتجديد حواجز الأمان الإسمنتية لحماية المركبات.',
            'department_id' => $roadsDept->id,
            'target_amount' => 250000.00,
            'current_amount' => 0.00, // Will be updated from contributions
            'status' => 'active',
            'latitude' => 15.3490000,
            'longitude' => 44.2080000,
            'created_by' => $admin->id,
        ]);

        ProjectPhase::create([
            'project_id' => $p1->id,
            'name' => 'الدراسات الإنشائية وفحص فواصل التمدد',
            'description' => 'المعاينة الهندسية الشاملة وتحديد الأجزاء الخرسانية المتآكلة.',
            'completion_percentage' => 100,
            'start_date' => now()->subMonths(2)->toDateString(),
            'end_date' => now()->subMonths(1)->toDateString(),
            'status' => 'completed',
        ]);

        ProjectPhase::create([
            'project_id' => $p1->id,
            'name' => 'أعمال التدعيم والصب الخرساني والحديد',
            'description' => 'توريد وتركيب الفواصل المعدنية وتدعيم الركائز الإسمنتية.',
            'completion_percentage' => 60,
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'status' => 'in_progress',
        ]);

        ProjectPhase::create([
            'project_id' => $p1->id,
            'name' => 'السفلتة النهائية وتخطيط مسارات السير',
            'description' => 'وضع الطبقة الإسفلتية الساخنة وتخطيط المسارات وتركيب عيون القطط الفوسفورية.',
            'completion_percentage' => 0,
            'start_date' => now()->addMonth()->toDateString(),
            'end_date' => now()->addMonths(2)->toDateString(),
            'status' => 'pending',
        ]);

        // Contributions for Project 1
        ProjectContribution::create([
            'project_id' => $p1->id,
            'contributor_id' => $citizen->id,
            'amount' => 15000.00,
            'created_at' => now()->subDays(10),
        ]);

        ProjectContribution::create([
            'project_id' => $p1->id,
            'contributor_id' => $citizen->id,
            'amount' => 35000.00,
            'created_at' => now()->subDays(5),
        ]);

        // Calculate and sync current_amount strictly with contributions
        $p1->update([
            'current_amount' => $p1->contributions()->sum('amount'),
        ]);

        // 2. Project 2: Solar Street Lighting
        $p2 = Project::create([
            'title' => 'مشروع تزويد الشوارع الحيوية بوحدات إنارة شمسية ذكية',
            'description' => 'تركيب 150 وحدة إنارة شمسية تعمل بأنظمة استشعار الحركة لترشيد الاستهلاك في الشوارع الفرعية.',
            'department_id' => $lightingDept->id,
            'target_amount' => 120000.00,
            'current_amount' => 0.00,
            'status' => 'published',
            'latitude' => 15.3350000,
            'longitude' => 44.2010000,
            'created_by' => $admin->id,
        ]);

        ProjectPhase::create([
            'project_id' => $p2->id,
            'name' => 'المسح الميداني وتحديد مواقع الأعمدة',
            'description' => 'تحديد النقاط الأكثر حاجة للإنارة لتأمين مسارات المشاة.',
            'completion_percentage' => 100,
            'start_date' => now()->subDays(20)->toDateString(),
            'end_date' => now()->subDays(5)->toDateString(),
            'status' => 'completed',
        ]);

        ProjectPhase::create([
            'project_id' => $p2->id,
            'name' => 'توريد الألواح والبطاريات ووحدات الـ LED',
            'description' => 'فحص كفاءة العينات والتأكد من مطابقتها للمواصفات الفنية المعتمدة.',
            'completion_percentage' => 25,
            'start_date' => now()->subDays(4)->toDateString(),
            'end_date' => now()->addDays(20)->toDateString(),
            'status' => 'in_progress',
        ]);

        ProjectContribution::create([
            'project_id' => $p2->id,
            'contributor_id' => $citizen->id,
            'amount' => 20000.00,
            'created_at' => now()->subDays(2),
        ]);

        // Calculate and sync current_amount strictly with contributions
        $p2->update([
            'current_amount' => $p2->contributions()->sum('amount'),
        ]);
    }
}
