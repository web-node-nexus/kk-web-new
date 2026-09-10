<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Ajax;
use App\Support\SiteSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings', [
            'user' => Auth::user(),
            'schema' => SiteSettings::schema(),
            'values' => SiteSettings::all(),
        ]);
    }

    public function update(Request $request): RedirectResponse|JsonResponse
    {
        $keys = [];
        foreach (SiteSettings::schema() as $group) {
            foreach ($group['fields'] as $field) {
                $keys[] = $field;
            }
        }

        $pairs = [];
        foreach ($keys as $field) {
            $key = $field['key'];
            if (($field['type'] ?? '') === 'toggle') {
                $pairs[$key] = $request->boolean($key) ? '1' : '0';
            } else {
                $pairs[$key] = trim((string) $request->input($key, ''));
            }
        }

        $request->validate([
            'company_name' => ['required', 'string', 'max:190'],
            'support_email' => ['required', 'email', 'max:190'],
            'default_hr_email' => ['nullable', 'email', 'max:190'],
            'late_checkin_after' => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
            'weekend_mode' => ['nullable', 'in:sat_sun,none,custom'],
            'weekend_days' => ['nullable', 'string', 'max:120'],
        ]);

        SiteSettings::setMany($pairs);
        SiteSettings::forget();

        return Ajax::ok($request, 'Settings saved. Changes apply immediately.', route('admin.settings'));
    }
}
