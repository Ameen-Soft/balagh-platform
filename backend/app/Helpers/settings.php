<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    /**
     * Retrieve a site setting by key, with fallback.
     */
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('setting_url')) {
    /**
     * Get the full public URL for a setting file stored in public disk.
     */
    function setting_url(string $key, ?string $default = null): ?string
    {
        $val = Setting::get($key);
        if (! $val) {
            return $default;
        }

        if (str_starts_with($val, 'http://') || str_starts_with($val, 'https://')) {
            return $val;
        }

        return asset('storage/'.ltrim($val, '/'));
    }
}
