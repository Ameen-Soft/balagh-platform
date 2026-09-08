<?php

namespace App\Services;

class GeoLocationService
{
    /**
     * Earth radius in meters.
     */
    protected const EARTH_RADIUS_METERS = 6371000;

    /**
     * Calculate the distance in meters between two coordinate pairs using the Haversine formula.
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round(self::EARTH_RADIUS_METERS * $c, 2);
    }

    /**
     * Check if two points are within a specified radius (in meters).
     */
    public function isWithinRadius(
        float $lat1,
        float $lon1,
        float $lat2,
        float $lon2,
        ?float $radiusMeters = null
    ): bool {
        $radius = $radiusMeters ?? (float) config('balagh.geo.field_worker_radius_meters', 500.0);
        $distance = $this->calculateDistance($lat1, $lon1, $lat2, $lon2);

        return $distance <= $radius;
    }

    /**
     * Validate that latitude and longitude are geometrically valid numbers.
     */
    public function isValidCoordinates(float $latitude, float $longitude): bool
    {
        return $latitude >= -90.0 && $latitude <= 90.0
            && $longitude >= -180.0 && $longitude <= 180.0;
    }
}
