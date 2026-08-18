<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Ajax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('admin.profile', [
            'user' => Auth::user(),
        ]);
    }

    public function update(Request $request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
        ]);

        $user->update(['name' => $data['name']]);

        return Ajax::ok($request, 'Profile updated.');
    }

    public function password(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();
        if (! \Illuminate\Support\Facades\Hash::check($data['current_password'], $user->password)) {
            return Ajax::fail($request, 'Current password is incorrect.', ['current_password' => ['Current password is incorrect.']]);
        }

        $user->update(['password' => $data['password']]);

        return Ajax::ok($request, 'Password updated.');
    }

    public function avatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [
            'avatar.required' => 'Pehle photo choose karo.',
            'avatar.image' => 'JPG, PNG ya WEBP photo lagao.',
            'avatar.max' => 'Photo max 4 MB honi chahiye.',
        ]);

        $user = Auth::user();
        $this->deleteAvatarFile($user);

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar_path' => $path]);

        return back()->with('success', 'Profile photo lag gayi.');
    }

    public function destroyAvatar(): RedirectResponse
    {
        $user = Auth::user();
        $this->deleteAvatarFile($user);
        $user->update(['avatar_path' => null]);

        return back()->with('success', 'Profile photo hata di.');
    }

    protected function deleteAvatarFile($user): void
    {
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }
    }
}
