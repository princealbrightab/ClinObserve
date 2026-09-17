<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfessorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isHod() && (! $this->route('professor') || $this->route('professor')->isProfessor());
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['email' => strtolower(trim((string) $this->input('email')))]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->route('professor'))],
            'password' => [$this->route('professor') ? 'prohibited' : 'required', Password::min(12)->letters()->numbers(), 'max:255'],
            'role' => ['prohibited'], 'is_active' => ['prohibited'], 'professor_id' => ['prohibited'], 'created_by' => ['prohibited'],
        ];
    }
}
