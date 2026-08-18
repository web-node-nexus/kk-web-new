<?php

namespace App\Support;

class PublicUrl
{
    public static function base(): string
    {
        $public = rtrim((string) config('app.public_url', ''), '/');
        if ($public !== '' && ! self::isLocal($public)) {
            return $public;
        }

        $app = rtrim((string) config('app.url', ''), '/');
        if ($app !== '' && ! self::isLocal($app)) {
            return $app;
        }

        return $public !== '' ? $public : ($app !== '' ? $app : 'https://kkdigitalsolution.com');
    }

    public static function to(string $path = '/'): string
    {
        $path = '/'.ltrim($path, '/');
        if ($path === '/') {
            return self::base();
        }

        return self::base().$path;
    }

    protected static function isLocal(string $url): bool
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return $host === ''
            || $host === 'localhost'
            || $host === '127.0.0.1'
            || $host === '::1'
            || str_ends_with($host, '.local');
    }
}
