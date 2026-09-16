<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HodReview extends Model
{
    use HasFactory;

    protected $fillable = ['comment'];

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(PatientEncounter::class, 'encounter_id');
    }

    public function hod(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hod_id');
    }
}
