<?php

namespace App\Http\Requests;

use App\Models\PatientEncounter;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class EncounterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->route('encounter') ? $this->user()->can('update', $this->route('encounter')) : $this->user()->can('create', PatientEncounter::class);
    }

    public function rules(): array
    {
        return [
            'attended_at' => ['required', 'date_format:Y-m-d\\TH:i'], 'summary' => ['required', 'string', 'min:10', 'max:5000'],
            'symptoms_observed' => ['nullable', 'string', 'max:5000'], 'examination_findings' => ['nullable', 'string', 'max:5000'],
            'assessment' => ['nullable', 'string', 'max:5000'], 'learning_notes' => ['nullable', 'string', 'max:5000'],
            'student_id' => ['prohibited'], 'patient_id' => ['prohibited'], 'locked_at' => ['prohibited'],
            'images' => ['nullable', 'array', 'max:5'], 'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=4000,max_height=4000'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->has('attended_at')) {
                return;
            }
            $date = CarbonImmutable::parse($this->input('attended_at'), config('clinobserve.timezone'));
            $patient = $this->route('patient') ?? $this->route('encounter')->patient;
            if ($date->isFuture() || $date->toDateString() < $patient->admission_date->toDateString()) {
                $validator->errors()->add('attended_at', 'Attendance must be after admission and not in the future.');
            }
        }];
    }
}
