<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    public const CACHE_KEY = 'site_settings_all';

    public $timestamps = false;

    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    protected static function booted(): void
    {
        static::saved(function () {
            static::clearCache();
        });

        static::deleted(function () {
            static::clearCache();
        });
    }

    /**
     * Clear settings cache.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Get all settings cached as a key => value collection.
     */
    public static function getAllCached(): Collection
    {
        $cached = Cache::get(self::CACHE_KEY);

        if (!is_array($cached)) {
            $cached = static::all()->pluck('value', 'key')->toArray();
            Cache::forever(self::CACHE_KEY, $cached);
        }

        return collect($cached);
    }

    /**
     * Get a setting by key with an optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $cached = static::getAllCached();

        if ($cached->has($key)) {
            $val = $cached->get($key);

            return ($val !== null && $val !== '') ? $val : $default;
        }

        return $default;
    }

    /**
     * Set a setting value by key.
     */
    public static function set(string $key, ?string $value, string $group = 'general'): self
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );

        static::clearCache();

        return $setting;
    }
}
