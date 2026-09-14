<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\ComplaintAttachment;
use App\Models\Department;
use App\Models\FieldAssignment;
use App\Models\Ministry;
use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\MinistryDepartmentSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class Phase01EnrichmentApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $citizen;
    protected User $ministryAdmin;
    protected User $fieldWorker;
    protected Department $department;
    protected Ministry $ministry;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(MinistryDepartmentSeeder::class);
        $this->seed(CategorySeeder::class);

        $this->ministry = Ministry::where('is_active', true)->first();
        $this->department = $this->ministry->departments()->where('is_active', true)->first();

        // Citizen
        $this->citizen = User::factory()->create(['is_active' => true]);
        $this->citizen->roles()->attach(Role::where('name', 'Citizen')->first()->id);

        // Ministry Admin
        $this->ministryAdmin = User::factory()->create([
            'department_id' => $this->department->id,
            'is_active' => true,
        ]);
        $this->ministryAdmin->roles()->attach(Role::where('name', 'Ministry Admin')->first()->id);

        // Field Worker
        $this->fieldWorker = User::factory()->create([
            'department_id' => $this->department->id,
            'is_active' => true,
        ]);
        $this->fieldWorker->roles()->attach(Role::where('name', 'Field Worker')->first()->id);

        $this->category = Category::where('department_id', $this->department->id)->first()
            ?? Category::whereNotNull('department_id')->first();
    }

    /**
     * Test 1: GET /api/v1/ministries returns active ministries list.
     */
    public function test_can_fetch_active_ministries(): void
    {
        $response = $this->getJson('/api/v1/ministries');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'code',
                        'is_active',
                        'departments',
                    ],
                ],
            ]);

        $this->assertNotEmpty($response->json('data'));
    }

    /**
     * Test 2: GET /api/v1/categories?ministry_id=X returns cascading categories.
     */
    public function test_can_fetch_categories_filtered_by_ministry(): void
    {
        $response = $this->getJson('/api/v1/categories?ministry_id=' . $this->ministry->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'level',
                        'parent_id',
                        'department_id',
                    ],
                ],
            ]);

        // Every category in the returned data must belong to the specified ministry
        foreach ($response->json('data') as $cat) {
            $department = Department::find($cat['department_id']);
            $this->assertEquals($this->ministry->id, $department->ministry_id);
        }
    }

    /**
     * Test 3: GET /api/v1/notifications returns authenticated user notifications with pagination.
     */
    public function test_authenticated_user_can_fetch_notifications(): void
    {
        // Create notifications for citizen
        Notification::create([
            'user_id' => $this->citizen->id,
            'title' => 'إشعار اختبار 1',
            'body' => 'محتوى الإشعار الأول',
            'type' => 'test',
            'is_read' => false,
        ]);

        Notification::create([
            'user_id' => $this->citizen->id,
            'title' => 'إشعار اختبار 2',
            'body' => 'محتوى الإشعار الثاني المقروء',
            'type' => 'test',
            'is_read' => true,
        ]);

        // Unauthenticated access returns 401
        $this->getJson('/api/v1/notifications')->assertStatus(401);

        // Authenticated access
        Sanctum::actingAs($this->citizen);

        $response = $this->getJson('/api/v1/notifications');
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'body',
                        'type',
                        'is_read',
                        'created_at',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'total',
                ],
            ]);

        $this->assertEquals(2, $response->json('meta.total'));

        // Test unread_only filter
        $unreadResponse = $this->getJson('/api/v1/notifications?unread_only=true');
        $unreadResponse->assertStatus(200);
        $this->assertEquals(1, count($unreadResponse->json('data')));
        $this->assertFalse($unreadResponse->json('data.0.is_read'));
    }

    /**
     * Test 4: PATCH /api/v1/notifications/{id}/read marks notification as read with authorization.
     */
    public function test_user_can_mark_notification_as_read_and_cannot_read_others(): void
    {
        $notification = Notification::create([
            'user_id' => $this->citizen->id,
            'title' => 'تحديث مهم',
            'body' => 'تفاصيل التحديث',
            'type' => 'status',
            'is_read' => false,
        ]);

        // Other user's notification
        $otherUser = User::factory()->create(['is_active' => true]);
        $otherNotification = Notification::create([
            'user_id' => $otherUser->id,
            'title' => 'إشعار خاص بمستخدم آخر',
            'body' => 'بيانات سرية',
            'type' => 'status',
            'is_read' => false,
        ]);

        Sanctum::actingAs($this->citizen);

        // Attempt to read other user's notification -> 403
        $this->patchJson("/api/v1/notifications/{$otherNotification->id}/read")
            ->assertStatus(403);

        // Mark own notification as read -> 200
        $response = $this->patchJson("/api/v1/notifications/{$notification->id}/read");
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $notification->id,
                    'is_read' => true,
                ],
            ]);

        $this->assertTrue($notification->fresh()->is_read);
    }

    /**
     * Test 5: PATCH /api/v1/field-assignments/{id}/accept accepts field assignment.
     */
    public function test_field_worker_can_accept_field_assignment(): void
    {
        $complaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->department->id,
            'title' => 'تسرب مياه في الحي',
            'description' => 'يوجد تسرب للمياه يحتاج معاينة ميدانية',
            'status' => 'assigned',
            'priority' => 'high',
            'latitude' => 15.369445,
            'longitude' => 44.191006,
        ]);

        $assignment = FieldAssignment::create([
            'complaint_id' => $complaint->id,
            'worker_id' => $this->fieldWorker->id,
            'assigned_by' => $this->ministryAdmin->id,
            'status' => 'pending',
            'notes' => 'يرجى التوجه فوراً',
        ]);

        // Unauthorized user cannot accept
        Sanctum::actingAs($this->citizen);
        $this->patchJson("/api/v1/field-assignments/{$assignment->id}/accept")
            ->assertStatus(403);

        // Assigned worker can accept
        Sanctum::actingAs($this->fieldWorker);
        $response = $this->patchJson("/api/v1/field-assignments/{$assignment->id}/accept");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $assignment->id,
                    'status' => 'accepted',
                ],
            ]);

        $this->assertEquals('accepted', $assignment->fresh()->status);
        $this->assertDatabaseHas('complaint_timeline', [
            'complaint_id' => $complaint->id,
            'new_value' => 'accepted',
        ]);
    }

    /**
     * Test 6: POST /api/v1/field-assignments/{id}/verify-location verifies worker proximity.
     */
    public function test_field_worker_location_verification_within_and_outside_range(): void
    {
        $complaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->department->id,
            'title' => 'انهيار رصيف المشاة',
            'description' => 'تضرر في الرصيف بحاجة إلى صيانة فورية',
            'status' => 'assigned',
            'priority' => 'medium',
            'latitude' => 15.369445,
            'longitude' => 44.191006,
        ]);

        $assignment = FieldAssignment::create([
            'complaint_id' => $complaint->id,
            'worker_id' => $this->fieldWorker->id,
            'assigned_by' => $this->ministryAdmin->id,
            'status' => 'accepted',
            'notes' => 'فحص الموقع',
        ]);

        Sanctum::actingAs($this->fieldWorker);

        // 1. Check coordinates within range (same coordinates -> ~0 meters)
        $validResponse = $this->postJson("/api/v1/field-assignments/{$assignment->id}/verify-location", [
            'latitude' => 15.369445,
            'longitude' => 44.191006,
        ]);

        $validResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'assignment_id' => $assignment->id,
                    'is_within_range' => true,
                ],
            ]);

        // 2. Check coordinates far outside range (approx 50+ km away)
        $farResponse = $this->postJson("/api/v1/field-assignments/{$assignment->id}/verify-location", [
            'latitude' => 15.800000,
            'longitude' => 44.500000,
        ]);

        $farResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'assignment_id' => $assignment->id,
                    'is_within_range' => false,
                ],
            ]);

        // 3. Validation failure when parameters are missing
        $this->postJson("/api/v1/field-assignments/{$assignment->id}/verify-location", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['latitude', 'longitude']);
    }

    /**
     * Test 7: Update complaint status with reopened and assigned.
     */
    public function test_complaint_status_update_supports_reopened_and_assigned(): void
    {
        $complaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->department->id,
            'title' => 'انقطاع المياه المتكرر',
            'description' => 'المشكلة عادت للظهور بعد الإغلاق',
            'status' => 'closed',
            'priority' => 'high',
            'latitude' => 15.369445,
            'longitude' => 44.191006,
        ]);

        Sanctum::actingAs($this->ministryAdmin);

        // Update to reopened
        $response = $this->patchJson("/api/v1/complaints/{$complaint->id}/status", [
            'status' => 'reopened',
            'notes' => 'إعادة فتح البلاغ بناءً على طلب المواطن واستمرار الخلل',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'reopened',
                ],
            ]);

        $this->assertEquals('reopened', $complaint->fresh()->status);
        $this->assertEquals('أُعيد فتحها', $complaint->fresh()->status_arabic);

        // Update to assigned
        $assignResponse = $this->patchJson("/api/v1/complaints/{$complaint->id}/status", [
            'status' => 'assigned',
            'notes' => 'توجيه البلاغ للبحث الميداني مجدداً',
        ]);

        $assignResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'assigned',
                ],
            ]);

        $this->assertEquals('assigned', $complaint->fresh()->status);
        $this->assertEquals('مسند للميدان', $complaint->fresh()->status_arabic);
    }

    /**
     * Test 8: Attachment types are saved as before and after.
     */
    public function test_attachment_types_conform_to_before_and_after(): void
    {
        Storage::fake('public');

        // Citizen creates complaint with attachment -> before
        Sanctum::actingAs($this->citizen);
        $file = UploadedFile::fake()->create('evidence.jpg', 100, 'image/jpeg');

        $response = $this->postJson('/api/v1/complaints', [
            'title' => 'حفرة عميقة في الشارع العام',
            'description' => 'الحفرة تتسبب في حوادث سير وتعيق حركة المشاة',
            'category_id' => $this->category->id,
            'latitude' => 15.369445,
            'longitude' => 44.191006,
            'attachments' => [$file],
        ]);

        $response->assertStatus(201);
        $complaintId = $response->json('data.id');

        $attachment = ComplaintAttachment::where('complaint_id', $complaintId)->first();
        $this->assertNotNull($attachment);
        $this->assertEquals('before', $attachment->type);

        // Field worker completes assignment with completion attachment -> after
        $assignment = FieldAssignment::create([
            'complaint_id' => $complaintId,
            'worker_id' => $this->fieldWorker->id,
            'assigned_by' => $this->ministryAdmin->id,
            'status' => 'in_progress',
        ]);

        Sanctum::actingAs($this->fieldWorker);
        $repairFile = UploadedFile::fake()->create('repair.jpg', 100, 'image/jpeg');

        $completeResponse = $this->postJson("/api/v1/field-assignments/{$assignment->id}/complete", [
            'report' => 'تم ردم الحفرة وتعبيد الطبقة الإسفلتية بالكامل بنجاح',
            'latitude' => 15.369445,
            'longitude' => 44.191006,
            'attachments' => [$repairFile],
        ]);

        $completeResponse->assertStatus(200);

        $afterAttachment = ComplaintAttachment::where('complaint_id', $complaintId)
            ->where('uploaded_by', $this->fieldWorker->id)
            ->first();

        $this->assertNotNull($afterAttachment);
        $this->assertEquals('after', $afterAttachment->type);
    }
}
