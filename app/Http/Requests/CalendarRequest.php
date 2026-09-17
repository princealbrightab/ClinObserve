<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CalendarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['month' => ['nullable', 'date_format:Y-m', 'after_or_equal:2000-01', 'before_or_equal:2100-12'], 'date' => ['nullable', 'date_format:Y-m-d'], 'student_id' => [$this->user()->isFaculty() ? 'nullable' : 'prohibited', 'integer', Rule::exists('users', 'id')->where('role', 'student')->when($this->user()->isProfessor(), fn ($rule) => $rule->where('professor_id', $this->user()->id))]];
    }
}
