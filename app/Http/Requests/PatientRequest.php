<?php

namespace App\Http\Requests;

use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;

class PatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->route('patient') ? $this->user()->can('update', $this->route('patient')) : $this->user()->can('create', Patient::class);
    }

    public function rules(): array
    {
        $rules = ['display_name' => ['required', 'string', 'max:120'], 'data_classification' => ['required', 'in:synthetic,deidentified'], 'gender' => ['required', 'in:female,male,other,unknown,undisclosed'], 'age_years' => ['required', 'integer', 'between:0,130'], 'admission_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'], 'condition' => ['nullable', 'string', 'max:150'], 'privacy_confirmed' => ['accepted'], 'created_by' => ['prohibited'], 'updated_by' => ['prohibited'], 'case_number' => ['prohibited']];
        foreach (Patient::CLINICAL_FIELDS as $field) {
            $rules[$field] = ['nullable', 'string', 'max:5000'];
        }

return $rules;
    }
}
