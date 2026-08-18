<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Throwable;

class AppTime
{
    public static function tz(): string
    {
        try {
            $tz = (string) (SiteSettings::get('timezone') ?: 'Asia/Kolkata');
            if ($tz !== '' && in_array($tz, timezone_identifiers_list(), true)) {
                return $tz;
            }
        } catch (Throwable) {
        }

        return 'Asia/Kolkata';
    }

    public static function now(): Carbon
    {
        return Carbon::now(self::tz());
    }

    /** Convert a stored UTC timestamp to India time without changing the instant. */
    public static function parse(?CarbonInterface $dt): ?Carbon
    {
        if (! $dt) {
            return null;
        }

        return Carbon::instance($dt)->copy()->timezone(self::tz());
    }

    /** Monday, 17 Aug 2026 · 06:30 PM */
    public static function format(?CarbonInterface $dt): string
    {
        $local = self::parse($dt);

        return $local ? $local->format('l, d M Y · h:i A') : '—';
    }

    /** Mon, 17 Aug 2026 · 06:30 PM */
    public static function formatShort(?CarbonInterface $dt): string
    {
        $local = self::parse($dt);

        return $local ? $local->format('D, d M Y · h:i A') : '—';
    }

    /** Monday, 17 Aug 2026 */
    public static function formatDate(?CarbonInterface $dt): string
    {
        $local = self::parse($dt);

        return $local ? $local->format('l, d M Y') : '—';
    }

    public static function formatNow(): string
    {
        return self::now()->format('l, d M Y · h:i A');
    }

    public static function fromDateAndTime(?string $date, ?string $time): ?Carbon
    {
        $date = trim((string) $date);
        if ($date === '') {
            return null;
        }

        $time = trim((string) $time);
        if ($time === '') {
            $time = '18:00';
        }
        if (strlen($time) === 5) {
            $time .= ':00';
        }

        return Carbon::createFromFormat('Y-m-d H:i:s', $date.' '.$time, self::tz());
    }
}
