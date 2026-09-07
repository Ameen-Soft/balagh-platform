<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\ComplaintAttachment;
use App\Models\ComplaintTimeline;
use App\Models\ComplaintTransfer;
use App\Models\Department;
use App\Models\FieldAssignment;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

class ComplaintSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Controlled seed strategy: only seed if no complaints exist
        if (Complaint::count() > 0) {
            return;
        }

        $citizen = User::where('email', 'citizen@example.test')->first();
        $ministryAdmin = User::where('email', 'ministry@example.test')->first();
        $worker = User::where('email', 'worker@example.test')->first();

        $roadsDept = Department::where('name', 'إدارة الطرق والجسور')->first();
        $lightingDept = Department::where('name', 'إدارة إنارة الشوارع والطاقة المتجددة')->first();
        $gridDept = Department::where('name', 'إدارة صيانة الشبكة الكهربائية')->first();
        $waterDept = Department::where('name', 'إدارة شبكات الإمداد المائي')->first();
        $sewageDept = Department::where('name', 'إدارة الصرف الصحي وتصريف السيول')->first();

        $potholesCat = Category::where('name', 'حفر وتشققات إسفلتية خطرة')->first();
        $bridgesCat = Category::where('name', 'انهيار أو تصدع في الجسور')->first();
        $lightingCat = Category::where('name', 'أعمدة إنارة معطلة في الشوارع')->first();
        $wiresCat = Category::where('name', 'أسلاك كهربائية مكشوفة أو محول خطر')->first();
        $waterLeakCat = Category::where('name', 'كسر أو تسرب في خطوط المياه الرئيسية')->first();
        $sewageCat = Category::where('name', 'طفح مياه الصرف الصحي (المجاري)')->first();

        // 1. Complaint: New
        $c1 = Complaint::create([
            'citizen_id' => $citizen->id,
            'category_id' => $potholesCat->id,
            'current_department_id' => $roadsDept->id,
            'title' => 'حفرة عميقة في شارع الزبيري أمام البنك العربي',
            'description' => 'توجد حفرة مفاجئة في المسار الأوسط تسببت في أضرار لعدة سيارات وتعيق انسيابية حركة المرور.',
            'status' => 'new',
            'priority' => 'high',
            'latitude' => 15.3524100,
            'longitude' => 44.2015200,
        ]);
        ComplaintTimeline::create([
            'complaint_id' => $c1->id,
            'event_type' => 'created',
            'description' => 'تم تقديم البلاغ من المواطن عبر تطبيق الهاتف مع التوثيق الجغرافي.',
            'performed_by' => $citizen->id,
            'created_at' => now()->subHours(5),
        ]);

        // 2. Complaint: Under Review
        $c2 = Complaint::create([
            'citizen_id' => $citizen->id,
            'category_id' => $bridgesCat->id,
            'current_department_id' => $roadsDept->id,
            'title' => 'تصدع وتآكل حديدي في جسر المشاة بشارع الستين',
            'description' => 'لوحظ اهتزاز غير طبيعي وتآكل في الدعامات السفلية لجسر المشاة قرب جولة مذبح.',
            'status' => 'under_review',
            'priority' => 'urgent',
            'latitude' => 15.3789000,
            'longitude' => 44.1834000,
        ]);
        ComplaintTimeline::create([
            'complaint_id' => $c2->id,
            'event_type' => 'created',
            'description' => 'تم استلام البلاغ برمجياً وتوجيهه لقسم الجسور.',
            'performed_by' => $citizen->id,
            'created_at' => now()->subHours(10),
        ]);
        ComplaintTimeline::create([
            'complaint_id' => $c2->id,
            'event_type' => 'status_changed',
            'description' => 'المشرف يراجع البلاغ للتحقق من أولويته الإنشائية.',
            'old_value' => 'new',
            'new_value' => 'under_review',
            'performed_by' => $ministryAdmin->id,
            'created_at' => now()->subHours(8),
        ]);

        // 3. Complaint: Assigned with FieldAssignment (pending)
        $c3 = Complaint::create([
            'citizen_id' => $citizen->id,
            'category_id' => $lightingCat->id,
            'current_department_id' => $lightingDept->id,
            'title' => 'انطفاء 6 أعمدة إنارة متتالية في شارع حدة',
            'description' => 'الشارع مظلم تماماً منذ 3 أيام مما أدى إلى وقوع حوادث سير متكررة.',
            'status' => 'assigned',
            'priority' => 'medium',
            'latitude' => 15.3341000,
            'longitude' => 44.2087000,
        ]);
        FieldAssignment::create([
            'complaint_id' => $c3->id,
            'worker_id' => $worker->id,
            'assigned_by' => $ministryAdmin->id,
            'status' => 'pending',
            'notes' => 'يرجى فحص القاطع الكهربائي المغذي للأعمدة.',
        ]);
        ComplaintTimeline::create([
            'complaint_id' => $c3->id,
            'event_type' => 'assigned',
            'description' => 'تم إسناد البلاغ للمهندس الميداني للفحص والمعالجة.',
            'new_value' => 'assigned',
            'performed_by' => $ministryAdmin->id,
            'created_at' => now()->subHours(6),
        ]);

        // 4. Complaint: In Progress with FieldAssignment & Before Attachment
        $c4 = Complaint::create([
            'citizen_id' => $citizen->id,
            'category_id' => $waterLeakCat->id,
            'current_department_id' => $waterDept->id,
            'title' => 'كسر أنبوب مياه رئيسي وتدفق المياه في حي الأصبحي',
            'description' => 'تدفق غزير للمياه في الشارع العام بعد انفجار المحبس الرئيسي.',
            'status' => 'in_progress',
            'priority' => 'urgent',
            'latitude' => 15.3123000,
            'longitude' => 44.2211000,
        ]);
        FieldAssignment::create([
            'complaint_id' => $c4->id,
            'worker_id' => $worker->id,
            'assigned_by' => $ministryAdmin->id,
            'status' => 'in_progress',
            'started_at' => now()->subHours(2),
            'notes' => 'تم إغلاق المحبس الاحتياطي وبدأت أعمال الحفر واستبدال الماسورة.',
        ]);
        ComplaintAttachment::create([
            'complaint_id' => $c4->id,
            'file_path' => 'complaints/evidence_water_leak_before.jpg',
            'file_type' => 'image/jpeg',
            'captured_latitude' => 15.3123000,
            'captured_longitude' => 44.2211000,
            'uploaded_by' => $citizen->id,
            'type' => 'before',
            'created_at' => now()->subHours(4),
        ]);
        ComplaintTimeline::create([
            'complaint_id' => $c4->id,
            'event_type' => 'evidence_uploaded',
            'description' => 'تم رفع الصورة التوثيقية للبلاغ من كاميرا التطبيق المباشرة.',
            'performed_by' => $citizen->id,
            'created_at' => now()->subHours(4),
        ]);

        // 5. Complaint: Resolved (with Before and After attachments + Completed Assignment)
        $c5 = Complaint::create([
            'citizen_id' => $citizen->id,
            'category_id' => $potholesCat->id,
            'current_department_id' => $roadsDept->id,
            'title' => 'إصلاح هبوط إسفلتي أمام مستشفى الثورة',
            'description' => 'تمت معالجة الهبوط الإسفلتي وسفلته الموقع وإعادة فتحه للمرور.',
            'status' => 'resolved',
            'priority' => 'high',
            'latitude' => 15.3567000,
            'longitude' => 44.2156000,
        ]);
        FieldAssignment::create([
            'complaint_id' => $c5->id,
            'worker_id' => $worker->id,
            'assigned_by' => $ministryAdmin->id,
            'status' => 'completed',
            'started_at' => now()->subDays(1),
            'completed_at' => now()->subHours(1),
            'notes' => 'تم دك التربة وسفلتة الموقع بمساحة 4 أمتار مربعة بنجاح.',
        ]);
        ComplaintAttachment::create([
            'complaint_id' => $c5->id,
            'file_path' => 'complaints/road_pothole_before.jpg',
            'file_type' => 'image/jpeg',
            'captured_latitude' => 15.3567000,
            'captured_longitude' => 44.2156000,
            'uploaded_by' => $citizen->id,
            'type' => 'before',
            'created_at' => now()->subDays(2),
        ]);
        ComplaintAttachment::create([
            'complaint_id' => $c5->id,
            'file_path' => 'complaints/road_pothole_after.jpg',
            'file_type' => 'image/jpeg',
            'captured_latitude' => 15.3567000,
            'captured_longitude' => 44.2156000,
            'uploaded_by' => $worker->id,
            'type' => 'after',
            'created_at' => now()->subHours(1),
        ]);
        ComplaintTimeline::create([
            'complaint_id' => $c5->id,
            'event_type' => 'resolved',
            'description' => 'تم إنجاز أعمال الصيانة الميدانية ورفع صورة ما بعد الإصلاح.',
            'old_value' => 'in_progress',
            'new_value' => 'resolved',
            'performed_by' => $worker->id,
            'created_at' => now()->subHours(1),
        ]);

        // 6. Complaint: Closed
        $c6 = Complaint::create([
            'citizen_id' => $citizen->id,
            'category_id' => $wiresCat->id,
            'current_department_id' => $gridDept->id,
            'title' => 'عزل كابلات مكشوفة قرب مدرسة المجد',
            'description' => 'تم عزل الكابلات وتأمين الصندوق الكهربائي بالكامل وإغلاق الطلب نهائياً.',
            'status' => 'closed',
            'priority' => 'urgent',
            'latitude' => 15.3411000,
            'longitude' => 44.1955000,
        ]);
        ComplaintTimeline::create([
            'complaint_id' => $c6->id,
            'event_type' => 'closed',
            'description' => 'تم إغلاق البلاغ بعد التأكد من زوال الخطر وارتياح المواطنين.',
            'old_value' => 'resolved',
            'new_value' => 'closed',
            'performed_by' => $ministryAdmin->id,
            'created_at' => now()->subDays(3),
        ]);

        // 7. Complaint: Rejected
        $c7 = Complaint::create([
            'citizen_id' => $citizen->id,
            'category_id' => $potholesCat->id,
            'current_department_id' => $roadsDept->id,
            'title' => 'بلاغ تجريبي غير واضح المعالم',
            'description' => 'صورة سوداء ومعلومات مضللة لا تطابق موقع الحادث.',
            'status' => 'rejected',
            'priority' => 'low',
            'latitude' => 15.3600000,
            'longitude' => 44.2000000,
        ]);
        ComplaintTimeline::create([
            'complaint_id' => $c7->id,
            'event_type' => 'rejected',
            'description' => 'تم رفض البلاغ لعدم وضوح الصورة وتكرار المحاولات الوهمية.',
            'old_value' => 'new',
            'new_value' => 'rejected',
            'performed_by' => $ministryAdmin->id,
            'created_at' => now()->subDays(1),
        ]);

        // 8. Complaint: Reopened
        $c8 = Complaint::create([
            'citizen_id' => $citizen->id,
            'category_id' => $sewageCat->id,
            'current_department_id' => $sewageDept->id,
            'title' => 'تجدد طفح الصرف الصحي في تقاطع الدائري',
            'description' => 'عادت مياه الصرف الصحي للطفح مجدداً بعد يومين من تسليك الخط.',
            'status' => 'reopened',
            'priority' => 'urgent',
            'latitude' => 15.3621000,
            'longitude' => 44.1912000,
        ]);
        ComplaintTimeline::create([
            'complaint_id' => $c8->id,
            'event_type' => 'reopened',
            'description' => 'تمت إعادة فتح البلاغ بناءً على طلب المواطن لاستمرار المشكلة.',
            'old_value' => 'closed',
            'new_value' => 'reopened',
            'performed_by' => $citizen->id,
            'created_at' => now()->subHours(3),
        ]);

        // 9. Complaint: Transferred between departments
        $c9 = Complaint::create([
            'citizen_id' => $citizen->id,
            'category_id' => $potholesCat->id,
            'current_department_id' => $roadsDept->id, // Now in Roads
            'title' => 'حفرة ناتجة عن أعمال كابلات سابقة في شارع بغداد',
            'description' => 'تم تقديم البلاغ لوزارة الكهرباء، وتم تحويله لوزارة الأشغال للاختصاص في إعادة السفلتة.',
            'status' => 'under_review',
            'priority' => 'medium',
            'latitude' => 15.3489000,
            'longitude' => 44.2033000,
        ]);
        ComplaintTransfer::create([
            'complaint_id' => $c9->id,
            'from_department_id' => $gridDept->id,
            'to_department_id' => $roadsDept->id,
            'reason' => 'تم الانتهاء من تمديد الكابلات وتتطلب الحالة إعادة سفلتة الشارع من قبل الأشغال.',
            'transferred_by' => $ministryAdmin->id,
            'created_at' => now()->subHours(12),
        ]);
        ComplaintTimeline::create([
            'complaint_id' => $c9->id,
            'event_type' => 'transferred',
            'description' => 'تم تحويل البلاغ من إدارة الكهرباء إلى إدارة الطرق والجسور لعدم الاختصاص.',
            'performed_by' => $ministryAdmin->id,
            'created_at' => now()->subHours(12),
        ]);

        // 10. Complaint: Duplicate of c1
        $c10 = Complaint::create([
            'citizen_id' => $citizen->id,
            'category_id' => $potholesCat->id,
            'current_department_id' => $roadsDept->id,
            'duplicate_of_id' => $c1->id, // Duplicate of Complaint 1
            'title' => 'تكرار: حفرة الزبيري أمام البنك العربي',
            'description' => 'مواطن آخر يبلغ عن نفس الحفرة المذكورة في البلاغ الرئيسي رقم '.$c1->id,
            'status' => 'new',
            'priority' => 'high',
            'latitude' => 15.3524150,
            'longitude' => 44.2015250,
        ]);
        ComplaintTimeline::create([
            'complaint_id' => $c10->id,
            'event_type' => 'created',
            'description' => 'تم إنشاء بلاغ مرتبط كبلاغ مكرر للبلاغ رقم #'.$c1->id,
            'performed_by' => $citizen->id,
            'created_at' => now()->subHours(1),
        ]);

        // Notifications
        Notification::create([
            'user_id' => $citizen->id,
            'title' => 'تم تحديث حالة بلاغك',
            'body' => 'تمت مباشرة العمل الميداني في بلاغك رقم #'.$c4->id.' بنجاح.',
            'type' => 'complaint_status',
            'data' => ['complaint_id' => $c4->id, 'new_status' => 'in_progress'],
            'is_read' => false,
            'created_at' => now()->subHours(2),
        ]);
    }
}
