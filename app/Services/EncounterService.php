<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\PatientEncounter;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class EncounterService
{
    public function __construct(private PrivateImageService $images) {}

    public function save(User $student, Patient $patient, array $data, array $uploads, ?PatientEncounter $encounter = null): PatientEncounter
    {
        $stored = [];
        try {
            return DB::transaction(function () use ($student, $patient, $data, $uploads, $encounter, &$stored): PatientEncounter {
                Patient::whereKey($patient->id)->lockForUpdate()->firstOrFail();
                if ($encounter) {
                    $encounter = PatientEncounter::whereKey($encounter->id)->lockForUpdate()->firstOrFail();
                    Gate::authorize('update', $encounter);
                } else {
                    Gate::authorize('create', PatientEncounter::class);
                    $encounter = new PatientEncounter;
                    $encounter->student_id = $student->id;
                    $encounter->patient_id = $patient->id;
                }
                $data['attended_at'] = CarbonImmutable::parse($data['attended_at'], config('clinobserve.timezone'))->utc();
                $encounter->fill(collect($data)->only(array_merge(['attended_at'], PatientEncounter::FIELDS))->all());
                $encounter->save();
                $this->addImages($encounter, $student, $uploads, $stored);

                return $encounter;
            });
        } catch (Throwable $e) {
            Storage::disk('clinical')->delete($stored);
            throw $e;
        }
    }

    public function upload(PatientEncounter $encounter, User $student, array $uploads): void
    {
        $stored = [];
        try {
            DB::transaction(function () use ($encounter, $student, $uploads, &$stored): void {
                $locked = PatientEncounter::whereKey($encounter->id)->lockForUpdate()->firstOrFail();
                Gate::authorize('update', $locked);
                $this->addImages($locked, $student, $uploads, $stored);
            });
        } catch (Throwable $e) {
            Storage::disk('clinical')->delete($stored);
            throw $e;
        }
    }

    private function addImages(PatientEncounter $encounter, User $student, array $uploads, array &$stored): void
    {
        if ($encounter->images()->count() + count($uploads) > 5) {
            throw ValidationException::withMessages(['images' => 'An encounter can contain at most five images.']);
        }
        foreach ($uploads as $file) {
            $metadata = $this->images->store($file, 'encounters');
            $stored[] = $metadata['file_path'];
            $image = $encounter->images()->make($metadata);
            $image->uploaded_by = $student->id;
            $image->save();
        }
    }
}
