<?php

namespace App\Support;

class SafeHtml
{
    public static function clean(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        $allowed = '<p><br><strong><b><em><i><u><ul><ol><li><a><h2><h3><blockquote><span>';

        return strip_tags($html, $allowed);
    }
}
