<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->route('hodReview') ? $this->user()->can('update', $this->route('hodReview')) : $this->user()->isHod();
    }

    public function rules(): array
    {
        return ['comment' => ['required', 'string', 'min:5', 'max:5000'], 'hod_id' => ['prohibited'], 'encounter_id' => ['prohibited']];
    }
}
