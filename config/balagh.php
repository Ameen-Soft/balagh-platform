<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Geolocation & Fencing Configuration
    |--------------------------------------------------------------------------
    */
    'geo' => [
        // Maximum allowed distance (meters) for a field worker to be considered "at the scene"
        'field_worker_radius_meters' => (float) env('BALAGH_FIELD_WORKER_RADIUS', 500),

        // Distance in meters to flag potentially duplicate complaints
        'duplicate_radius_meters' => (float) env('BALAGH_DUPLICATE_RADIUS', 100),

        // Time window in days for duplicate checking
        'duplicate_days_window' => (int) env('BALAGH_DUPLICATE_DAYS', 7),

        // String similarity percentage threshold (0-100)
        'duplicate_text_threshold' => (float) env('BALAGH_DUPLICATE_TEXT_THRESHOLD', 75.0),
    ],

    /*
    |--------------------------------------------------------------------------
    | File Uploads & Storage Configuration
    |--------------------------------------------------------------------------
    */
    'storage' => [
        'disk' => env('BALAGH_STORAGE_DISK', 'public'),
        'complaints_folder' => 'complaints',
        'evidence_folder' => 'field_evidence',
        'projects_folder' => 'projects',
        'max_file_size_kb' => 10240, // 10MB
        'allowed_mimes' => ['jpeg', 'png', 'jpg', 'pdf', 'mp4'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Routing & Organizational Defaults
    |--------------------------------------------------------------------------
    */
    'routing' => [
        // Fallback ministry ID if category has no direct ministry mapped
        'fallback_ministry_id' => (int) env('BALAGH_FALLBACK_MINISTRY_ID', 1),

        // Fallback department ID if category has no direct department mapped
        'fallback_department_id' => (int) env('BALAGH_FALLBACK_DEPARTMENT_ID', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Tracking & Codes
    |--------------------------------------------------------------------------
    */
    'tracking' => [
        'prefix' => env('BALAGH_TRACKING_PREFIX', 'BLG'),
    ],

];
