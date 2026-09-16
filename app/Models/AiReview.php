<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiReview extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'encounter_id', 'requested_by'];

    protected function casts(): array
    {
        return ['input_snapshot' => 'array', 'structured_response' => 'array', 'privacy_confirmed_at' => 'datetime', 'completed_at' => 'datetime'];
    }

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(PatientEncounter::class, 'encounter_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
