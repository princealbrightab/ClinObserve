<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->route('student') ? $this->user()->can('update', $this->route('student')) : $this->user()->can('create', User::class);
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['email' => strtolower(trim((string) $this->input('email')))]);
    }

    public function rules(): array
    {
        $student = $this->route('student');

        return [
            'name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($student)],
            'password' => [$student ? 'prohibited' : 'required', Password::min(12)->letters()->numbers(), 'max:255'],
            'roll_number' => ['required', 'string', 'max:50', Rule::unique('student_profiles')->ignore($student?->studentProfile?->id)],
            'registration_number' => ['nullable', 'string', 'max:100', Rule::unique('student_profiles')->ignore($student?->studentProfile?->id)],
            'batch' => ['required', 'string', 'max:50'], 'academic_year' => ['required', 'string', 'max:20'],
            'college' => ['nullable', 'string', 'max:150'], 'course' => ['nullable', 'string', 'max:150'], 'department' => ['nullable', 'string', 'max:150'],
            'joining_year' => ['nullable', 'integer', 'between:1950,2100'], 'role' => ['prohibited'], 'user_id' => ['prohibited'], 'is_active' => ['prohibited'],
        ];
    }
}
