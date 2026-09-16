<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use App\Models\User;
use App\Services\PrivateImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile', ['user' => $request->user()->load('studentProfile')]);
    }

    public function update(ProfileRequest $request, PrivateImageService $images): RedirectResponse
    {
        $profile = $request->user()->studentProfile;
        abort_unless($profile, 404);
        $old = $profile->avatar_path;
        $path = null;
        try {
            if ($request->hasFile('avatar')) {
                $path = $images->store($request->file('avatar'), 'avatars')['file_path'];
                $profile->avatar_path = $path;
            }
            $profile->fill($request->safe()->except('avatar'))->save();
        } catch (Throwable $e) {
            if ($path) {
                Storage::disk('clinical')->delete($path);
            } throw $e;
        }
        if ($path && $old) {
            Storage::disk('clinical')->delete($old);
        }

        return back()->with('success', 'Profile updated.');
    }

    public function avatar(User $student): StreamedResponse
    {
        Gate::authorize('view', $student);
        $path = $student->studentProfile?->avatar_path;
        abort_unless($path && Storage::disk('clinical')->exists($path), 404);

        return Storage::disk('clinical')->response($path, 'avatar.png', ['Content-Type' => 'image/png', 'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
