<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImageRequest;
use App\Models\EncounterImage;
use App\Models\PatientEncounter;
use App\Services\EncounterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImageController extends Controller
{
    public function show(EncounterImage $image): StreamedResponse
    {
        Gate::authorize('view', $image);
        abort_unless(Storage::disk('clinical')->exists($image->file_path), 404);

        return Storage::disk('clinical')->response($image->file_path, 'observation.png', ['Content-Type' => $image->mime_type, 'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }

    public function store(ImageRequest $request, PatientEncounter $encounter, EncounterService $service): RedirectResponse
    {
        $service->upload($encounter, $request->user(), $request->file('images'));

        return back()->with('success', 'Images uploaded.');
    }

    public function destroy(EncounterImage $image): RedirectResponse
    {
        DB::transaction(function () use ($image): void {
            $encounter = PatientEncounter::whereKey($image->encounter_id)->lockForUpdate()->firstOrFail();
            Gate::authorize('update', $encounter);
            $image->delete();
        });
        Storage::disk('clinical')->delete($image->file_path);

        return back()->with('success', 'Image removed.');
    }
}
