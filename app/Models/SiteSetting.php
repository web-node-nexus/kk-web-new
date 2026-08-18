<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public const CACHE_KEY = 'kk_site_setting_rows';

    /** @return array<string, mixed> */
    public static function map(): array
    {
        return Cache::remember(self::CACHE_KEY, 300, function () {
            return static::query()->pluck('value', 'key')->all();
        });
    }

    public static function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget(\App\Support\SiteSettings::CACHE_KEY);
    }

    /**
     * Key-value getter (JSON-aware). Used by PageController / AppServiceProvider.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $raw = static::map()[$key] ?? null;
        if ($raw === null) {
            return $default;
        }

        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return $raw;
    }

    public static function put(string $key, mixed $value): void
    {
        $stored = is_array($value) || is_object($value)
            ? json_encode($value)
            : (string) $value;

        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $stored]
        );

        static::forgetCache();
    }
}
