<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ValidationService
{
    public function __construct(
        protected GeoLocationService $geoService
    ) {}

    /**
     * Validate business-level rules before filing a complaint.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function validateComplaintBusinessRules(array $data, User $user): void
    {
        // 1. Citizen account state check
        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'citizen' => ['حساب المستخدم معطل ولا يمكنه تقديم بلاغات حالياً.'],
            ]);
        }

        // 2. Geographic coordinates sanity check
        $latitude = (float) $data['latitude'];
        $longitude = (float) $data['longitude'];

        if (! $this->geoService->isValidCoordinates($latitude, $longitude)) {
            throw ValidationException::withMessages([
                'coordinates' => ['الإحداثيات الجغرافية غير صحيحة أو خارج النطاق المنطقي للأرض.'],
            ]);
        }

        // 3. Category existence, hierarchy, active state, and ministry integrity validation
        $category = Category::with(['parent', 'department'])->find($data['category_id']);
        if (! $category) {
            throw ValidationException::withMessages([
                'category_id' => ['التصنيف المختار غير متوفر في النظام.'],
            ]);
        }

        if ($category->level === 1 || empty($category->parent_id)) {
            throw ValidationException::withMessages([
                'category_id' => ['لا يمكن تقديم بلاغ على تصنيف رئيسي مباشرة؛ يرجى اختيار نوع المشكلة من التصنيفات الفرعية.'],
            ]);
        }

        if (! $category->is_active) {
            throw ValidationException::withMessages([
                'category_id' => ['هذا التصنيف معطل حالياً ولا يمكن استقبال بلاغات جديدة عليه.'],
            ]);
        }

        if ($category->parent && ! $category->parent->is_active) {
            throw ValidationException::withMessages([
                'category_id' => ['التصنيف الرئيسي التابع له هذا التصنيف معطل حالياً.'],
            ]);
        }

        if (! $category->department_id || ! $category->department || ! $category->department->is_active) {
            throw ValidationException::withMessages([
                'category_id' => ['الإدارة الخدمية المسؤولة عن هذا التصنيف غير متوفرة أو معطلة حالياً.'],
            ]);
        }

        $minId = $category->ministry_id ?? $category->parent?->ministry_id;
        if ($minId && $category->department->ministry_id !== $minId) {
            throw ValidationException::withMessages([
                'category_id' => ['بيانات التصنيف غير متوافقة مع الإدارة المسؤولة أو الوزارة التابعة لها.'],
            ]);
        }

        // 4. Rate-limiting / Flood prevention (no more than 5 complaints within 15 minutes)
        $recentCount = Complaint::where('citizen_id', $user->id)
            ->where('created_at', '>=', now()->subMinutes(15))
            ->count();

        if ($recentCount >= 5) {
            throw ValidationException::withMessages([
                'rate_limit' => ['لقد تجاوزت الحد المسموح به من البلاغات خلال فترة قصيرة (5 بلاغات في 15 دقيقة). يرجى الانتظار قليلاً.'],
            ]);
        }
    }
}
