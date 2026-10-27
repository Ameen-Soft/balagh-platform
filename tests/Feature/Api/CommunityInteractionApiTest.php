<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\ComplaintLike;
use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\MinistryDepartmentSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class CommunityInteractionApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $citizen;

    protected User $citizenTwo;

    protected User $admin;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(MinistryDepartmentSeeder::class);
        $this->seed(CategorySeeder::class);

        $citizenRole = Role::where('name', 'Citizen')->first();
        $adminRole = Role::where('name', 'Ministry Admin')->first();

        $this->citizen = User::factory()->create([
            'name' => 'المواطن أحمد',
            'phone' => '771234567',
            'is_active' => true,
        ]);
        $this->citizen->roles()->attach($citizenRole->id);

        $this->citizenTwo = User::factory()->create([
            'name' => 'المواطن سمير',
            'phone' => '772345678',
            'is_active' => true,
        ]);
        $this->citizenTwo->roles()->attach($citizenRole->id);

        $this->category = Category::whereNotNull('department_id')->first();

        $this->admin = User::factory()->create([
            'name' => 'مشرف الوزارة',
            'department_id' => $this->category->department_id,
            'is_active' => true,
        ]);
        $this->admin->roles()->attach($adminRole->id);
    }

    public function test_citizen_can_create_public_complaint(): void
    {
        $payload = [
            'title' => 'انكسار أنبوب مياه رئيسي بالحي',
            'description' => 'تسرب مياه شديد يغمر الشارع الرئيسي بحاجة لتدخل عاجل.',
            'category_id' => $this->category->id,
            'latitude' => 15.369445,
            'longitude' => 44.191007,
            'priority' => 'medium',
            'is_public' => true,
        ];

        $response = $this->actingAs($this->citizen, 'sanctum')
            ->postJson('/api/v1/complaints', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('complaints', [
            'title' => 'انكسار أنبوب مياه رئيسي بالحي',
            'is_public' => 1,
            'likes_count' => 0,
        ]);
    }

    public function test_public_feed_only_displays_approved_public_complaints_without_pii(): void
    {
        // 1. Public and approved (under_review)
        $publicApproved = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->category->department_id,
            'title' => 'بلاغ عام معتمد',
            'description' => 'وصف تفصيلي للبلاغ العام المعتمد',
            'status' => 'under_review',
            'priority' => 'medium',
            'latitude' => 15.350000,
            'longitude' => 44.200000,
            'is_public' => true,
            'likes_count' => 5,
        ]);

        // 2. Private complaint
        $privateComplaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->category->department_id,
            'title' => 'بلاغ خاص جداً',
            'description' => 'هذا البلاغ خاص ولا يجب أن يظهر للعامة',
            'status' => 'under_review',
            'priority' => 'medium',
            'latitude' => 15.351000,
            'longitude' => 44.201000,
            'is_public' => false,
            'likes_count' => 0,
        ]);

        // 3. Rejected public complaint
        $rejectedComplaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->category->department_id,
            'title' => 'بلاغ عام مرفوض',
            'description' => 'هذا البلاغ مرفوض ولا يجب أن يظهر في الخلاصة العامة',
            'status' => 'rejected',
            'priority' => 'low',
            'latitude' => 15.352000,
            'longitude' => 44.202000,
            'is_public' => true,
            'likes_count' => 2,
        ]);

        $response = $this->getJson('/api/v1/complaints/public');

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        $titles = collect($response->json('data'))->pluck('title')->all();
        $this->assertContains('بلاغ عام معتمد', $titles);
        $this->assertNotContains('بلاغ خاص جداً', $titles);
        $this->assertNotContains('بلاغ عام مرفوض', $titles);

        // Verify citizen sensitive PII is redacted from public payload
        $firstItem = $response->json('data.0');
        $this->assertArrayNotHasKey('citizen', $firstItem);
        $this->assertArrayNotHasKey('phone', $firstItem);
        $this->assertArrayNotHasKey('national_id', $firstItem);
    }

    public function test_authenticated_citizen_can_like_and_unlike_public_complaint(): void
    {
        $complaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->category->department_id,
            'title' => 'حفرة عميقة في التقاطع',
            'description' => 'حفرة خطرة تهدد السلامة العامة في تقاطع الشارع الرئيسي.',
            'status' => 'assigned',
            'priority' => 'medium',
            'latitude' => 15.360000,
            'longitude' => 44.210000,
            'is_public' => true,
            'likes_count' => 0,
        ]);

        // Like as citizenTwo
        $likeResponse = $this->actingAs($this->citizenTwo, 'sanctum')
            ->postJson("/api/v1/complaints/{$complaint->id}/like");

        $likeResponse->assertStatus(200);
        $likeResponse->assertJsonPath('data.liked', true);
        $likeResponse->assertJsonPath('data.likes_count', 1);

        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'likes_count' => 1,
        ]);
        $this->assertDatabaseHas('complaint_likes', [
            'complaint_id' => $complaint->id,
            'user_id' => $this->citizenTwo->id,
        ]);

        // Liking again is idempotent
        $duplicateLikeResponse = $this->actingAs($this->citizenTwo, 'sanctum')
            ->postJson("/api/v1/complaints/{$complaint->id}/like");

        $duplicateLikeResponse->assertStatus(200);
        $duplicateLikeResponse->assertJsonPath('data.already_liked', true);
        $duplicateLikeResponse->assertJsonPath('data.likes_count', 1);

        // Inspect public details with is_liked_by_me = true
        $showResponse = $this->actingAs($this->citizenTwo, 'sanctum')
            ->getJson("/api/v1/complaints/public/{$complaint->id}");

        $showResponse->assertStatus(200);
        $showResponse->assertJsonPath('data.is_liked_by_me', true);
        $showResponse->assertJsonPath('data.likes_count', 1);

        // Unlike
        $unlikeResponse = $this->actingAs($this->citizenTwo, 'sanctum')
            ->deleteJson("/api/v1/complaints/{$complaint->id}/like");

        $unlikeResponse->assertStatus(200);
        $unlikeResponse->assertJsonPath('data.liked', false);
        $unlikeResponse->assertJsonPath('data.likes_count', 0);

        $this->assertDatabaseMissing('complaint_likes', [
            'complaint_id' => $complaint->id,
            'user_id' => $this->citizenTwo->id,
        ]);

        // Unliking again should remain 0 and not drop negative
        $secondUnlikeResponse = $this->actingAs($this->citizenTwo, 'sanctum')
            ->deleteJson("/api/v1/complaints/{$complaint->id}/like");

        $secondUnlikeResponse->assertStatus(200);
        $secondUnlikeResponse->assertJsonPath('data.likes_count', 0);
    }

    public function test_guest_cannot_like_complaint(): void
    {
        $complaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->category->department_id,
            'title' => 'عمود إنارة مائل',
            'description' => 'عمود إنارة آيل للسقوط بجانب مدرسة.',
            'status' => 'in_progress',
            'priority' => 'high',
            'latitude' => 15.370000,
            'longitude' => 44.220000,
            'is_public' => true,
            'likes_count' => 0,
        ]);

        $response = $this->postJson("/api/v1/complaints/{$complaint->id}/like");
        $response->assertStatus(401);
    }

    public function test_trending_alert_triggers_notification_without_distorting_official_priority(): void
    {
        // Threshold is configured as 15
        config(['balagh.community.trending_threshold' => 3]);

        $complaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->category->department_id,
            'title' => 'تجمع مياه راكدة يسبب أوبئة',
            'description' => 'تجمع مياه مجاري راكدة أمام المستوصف الصحي بالحي.',
            'status' => 'under_review',
            'priority' => 'low', // Official priority is low
            'latitude' => 15.380000,
            'longitude' => 44.230000,
            'is_public' => true,
            'likes_count' => 0,
        ]);

        // 3 citizens like the complaint
        for ($i = 1; $i <= 3; $i++) {
            $user = User::factory()->create(['is_active' => true]);
            $this->actingAs($user, 'sanctum')
                ->postJson("/api/v1/complaints/{$complaint->id}/like")
                ->assertStatus(200);
        }

        $complaint->refresh();
        $this->assertEquals(3, $complaint->likes_count);
        $this->assertNotNull($complaint->community_alerted_at);

        // Crucial Architectural Check: Official priority remains unchanged!
        $this->assertEquals('low', $complaint->priority);

        // Verify timeline log event
        $this->assertDatabaseHas('complaint_timeline', [
            'complaint_id' => $complaint->id,
            'event_type' => 'community_trending_alert',
        ]);

        // Verify notification dispatched to admin
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->admin->id,
            'type' => 'community_trending_alert',
        ]);
    }

    public function test_map_endpoint_returns_lightweight_pins_with_bbox_filter(): void
    {
        Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->category->department_id,
            'title' => 'نقطة داخل صنعاء',
            'description' => 'وصف لنقطة داخل النطاق الجغرافي المحدد',
            'status' => 'resolved',
            'priority' => 'medium',
            'latitude' => 15.350000,
            'longitude' => 44.200000,
            'is_public' => true,
            'likes_count' => 10,
        ]);

        Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->category->department_id,
            'title' => 'نقطة خارج النطاق في عدن',
            'description' => 'وصف لنقطة خارج النطاق الجغرافي المحدد',
            'status' => 'resolved',
            'priority' => 'medium',
            'latitude' => 12.800000,
            'longitude' => 45.000000,
            'is_public' => true,
            'likes_count' => 4,
        ]);

        // Query with bbox covering Sanaa only: min_lat,min_lng,max_lat,max_lng
        $response = $this->getJson('/api/v1/complaints/map?bbox=15.0,44.0,16.0,45.0');

        $response->assertStatus(200);
        $pins = $response->json('data');
        $this->assertCount(1, $pins);
        $this->assertEquals('نقطة داخل صنعاء', $pins[0]['title']);
    }

    public function test_artisan_reconciliation_command_corrects_discrepancies(): void
    {
        $complaint = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->category->department_id,
            'title' => 'بلاغ للتسوية',
            'description' => 'اختبار أمر تسوية عداد الإعجابات في النظام',
            'status' => 'in_progress',
            'priority' => 'high',
            'latitude' => 15.350000,
            'longitude' => 44.200000,
            'is_public' => true,
            'likes_count' => 99, // Desynchronized count
        ]);

        ComplaintLike::create([
            'complaint_id' => $complaint->id,
            'user_id' => $this->citizen->id,
        ]);
        ComplaintLike::create([
            'complaint_id' => $complaint->id,
            'user_id' => $this->citizenTwo->id,
        ]);

        // Run artisan command
        Artisan::call('complaints:reconcile-likes', [
            '--complaint_id' => $complaint->id,
        ]);

        $complaint->refresh();
        $this->assertEquals(2, $complaint->likes_count);
    }

    public function test_public_feed_reflects_is_liked_by_me_with_bearer_token(): void
    {
        $complaintLiked = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->category->department_id,
            'title' => 'بلاغ تم الإعجاب به',
            'description' => 'وصف البلاغ الذي أعجب به المواطن سمير',
            'status' => 'in_progress',
            'priority' => 'medium',
            'latitude' => 15.350000,
            'longitude' => 44.200000,
            'is_public' => true,
            'likes_count' => 1,
        ]);

        $complaintNotLiked = Complaint::create([
            'citizen_id' => $this->citizen->id,
            'category_id' => $this->category->id,
            'current_department_id' => $this->category->department_id,
            'title' => 'بلاغ لم يتم الإعجاب به',
            'description' => 'وصف البلاغ الآخر الذي لم يعجب به أحد',
            'status' => 'in_progress',
            'priority' => 'low',
            'latitude' => 15.360000,
            'longitude' => 44.210000,
            'is_public' => true,
            'likes_count' => 0,
        ]);

        ComplaintLike::create([
            'complaint_id' => $complaintLiked->id,
            'user_id' => $this->citizenTwo->id,
        ]);

        // Generate Sanctum Bearer token for citizenTwo
        $token = $this->citizenTwo->createToken('test-bearer-token')->plainTextToken;

        // 1. Fetch public feed with Bearer token
        $authResponse = $this->withToken($token)->getJson('/api/v1/complaints/public');
        $authResponse->assertStatus(200);

        $data = collect($authResponse->json('data'));
        $likedItem = $data->firstWhere('id', $complaintLiked->id);
        $notLikedItem = $data->firstWhere('id', $complaintNotLiked->id);

        $this->assertNotNull($likedItem);
        $this->assertNotNull($notLikedItem);
        $this->assertTrue($likedItem['is_liked_by_me'], 'Public feed must reflect is_liked_by_me=true when user has liked the complaint');
        $this->assertFalse($notLikedItem['is_liked_by_me'], 'Public feed must reflect is_liked_by_me=false when user has not liked the complaint');

        // 2. Fetch single public complaint with Bearer token
        $showResponse = $this->withToken($token)->getJson("/api/v1/complaints/public/{$complaintLiked->id}");
        $showResponse->assertStatus(200);
        $showResponse->assertJsonPath('data.is_liked_by_me', true);

        // 3. Guest request (without token) must receive false for both
        $this->flushHeaders();
        auth('sanctum')->forgetUser();

        $guestResponse = $this->getJson('/api/v1/complaints/public');
        $guestResponse->assertStatus(200);
        $guestData = collect($guestResponse->json('data'));
        $guestLikedItem = $guestData->firstWhere('id', $complaintLiked->id);
        $this->assertFalse($guestLikedItem['is_liked_by_me'], 'Guest request must receive is_liked_by_me=false');
    }
}
