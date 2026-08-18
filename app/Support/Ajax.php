<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class Ajax
{
    public static function wants(Request $request): bool
    {
        return $request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest';
    }

    public static function ok(Request $request, string $message, ?string $redirect = null, array $extra = []): JsonResponse|RedirectResponse
    {
        if (self::wants($request)) {
            return response()->json(array_merge([
                'ok' => true,
                'message' => $message,
                'redirect' => $redirect,
            ], $extra));
        }

        $go = $redirect ? redirect()->to($redirect) : back();

        return $go->with('success', $message);
    }

    public static function fail(Request $request, string $message, array $errors = [], int $status = 422): JsonResponse|RedirectResponse
    {
        if (self::wants($request)) {
            return response()->json([
                'ok' => false,
                'message' => $message,
                'errors' => $errors ?: ['form' => [$message]],
            ], $status);
        }

        return back()->withInput()->withErrors($errors ?: ['form' => $message]);
    }
}
