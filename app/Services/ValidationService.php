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

        // 3. Category existence and active validation
        $category = Category::find($data['category_id']);
        if (! $category) {
            throw ValidationException::withMessages([
                'category_id' => ['التصنيف المختار غير متوفر في النظام.'],
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
