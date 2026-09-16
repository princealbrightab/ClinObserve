<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AiReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('requestAi', $this->route('encounter'));
    }

    public function rules(): array
    {
        return ['request_key' => ['required', 'uuid'], 'privacy_confirmed' => ['accepted'], 'input_hash' => ['required', 'string', 'size:64']];
    }
}
