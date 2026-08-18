<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskMedia
{
    public static function rules(): array
    {
        return [
            'pdf' => ['nullable', 'file', 'mimes:pdf', 'max:15360'],
            'video' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime,video/x-msvideo,video/mpeg', 'max:51200'],
            'file' => ['nullable', 'file', 'max:20480'],
            'link_url' => ['nullable', 'url', 'max:500'],
            'meet_link' => ['nullable', 'url', 'max:500'],
        ];
    }

    public static function store(Request $request, string $folder): array
    {
        $out = [
            'link_url' => $request->filled('link_url') ? $request->string('link_url')->toString() : null,
            'meet_link' => $request->filled('meet_link') ? $request->string('meet_link')->toString() : null,
        ];

        foreach (['pdf', 'video', 'file'] as $key) {
            if ($request->hasFile($key)) {
                $uploaded = $request->file($key);
                $out[$key.'_path'] = $uploaded->store($folder, 'public');
                $out[$key.'_name'] = $uploaded->getClientOriginalName();
            }
        }

        return $out;
    }

    public static function hasAny(object $item): bool
    {
        return filled($item->pdf_path ?? null)
            || filled($item->video_path ?? null)
            || filled($item->file_path ?? null)
            || filled($item->link_url ?? null)
            || filled($item->meet_link ?? null);
    }

    public static function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
