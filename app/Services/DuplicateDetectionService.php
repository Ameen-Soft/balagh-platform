<?php

namespace App\Services;

use App\Models\Complaint;

class DuplicateDetectionService
{
    public function __construct(
        protected GeoLocationService $geoService
    ) {}

    /**
     * Check if a new complaint might be a duplicate of an existing active complaint.
     *
     * @param  array{category_id: int, latitude: float|string, longitude: float|string, title: string, description: string}  $data
     * @return array{is_duplicate: bool, matched_complaint_id: ?int, match_score: float, distance_meters: ?float}
     */
    public function check(array $data): array
    {
        $radius = (float) config('balagh.geo.duplicate_radius_meters', 100.0);
        $daysWindow = (int) config('balagh.geo.duplicate_days_window', 7);
        $threshold = (float) config('balagh.geo.duplicate_text_threshold', 75.0);

        $targetLat = (float) $data['latitude'];
        $targetLon = (float) $data['longitude'];
        $targetText = trim(($data['title'] ?? '') . ' ' . ($data['description'] ?? ''));

        // Query active complaints within the recent time window in the same category
        $candidates = Complaint::where('category_id', $data['category_id'])
            ->where('created_at', '>=', now()->subDays($daysWindow))
            ->whereNotIn('status', ['rejected', 'closed'])
            ->get(['id', 'title', 'description', 'latitude', 'longitude']);

        $bestMatchId = null;
        $bestMatchScore = 0.0;
        $bestDistance = null;

        foreach ($candidates as $candidate) {
            $distance = $this->geoService->calculateDistance(
                $targetLat,
                $targetLon,
                (float) $candidate->latitude,
                (float) $candidate->longitude
            );

            // Must be within geographical proximity radius
            if ($distance <= $radius) {
                $candidateText = trim($candidate->title . ' ' . $candidate->description);
                similar_text($targetText, $candidateText, $textPercent);

                // Geographic score component (0 to 100, where closer is higher)
                $geoScore = max(0, 100 - ($distance / max(1, $radius)) * 50);

                // Composite match score: 50% text similarity, 50% geographic proximity
                $score = round((0.5 * $textPercent) + (0.5 * $geoScore), 2);

                if ($score > $bestMatchScore) {
                    $bestMatchScore = $score;
                    $bestMatchId = $candidate->id;
                    $bestDistance = $distance;
                }
            }
        }

        $isDuplicate = ($bestMatchScore >= $threshold && $bestMatchId !== null);

        return [
            'is_duplicate' => $isDuplicate,
            'matched_complaint_id' => $bestMatchId,
            'match_score' => $bestMatchScore,
            'distance_meters' => $bestDistance,
        ];
    }
}
