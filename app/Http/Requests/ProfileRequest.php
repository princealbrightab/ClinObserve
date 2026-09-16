<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isStudent();
    }

    public function rules(): array
    {
        return [
            'phone' => ['nullable', 'string', 'max:30'], 'date_of_birth' => ['nullable', 'date_format:Y-m-d', 'before:today'],
            'gender' => ['nullable', 'in:female,male,other,unknown,undisclosed'], 'address' => ['nullable', 'string', 'max:2000'], 'bio' => ['nullable', 'string', 'max:3000'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=4000,max_height=4000'],
            'role' => ['prohibited'], 'user_id' => ['prohibited'], 'name' => ['prohibited'], 'email' => ['prohibited'], 'roll_number' => ['prohibited'], 'is_active' => ['prohibited'],
        ];
    }
}
