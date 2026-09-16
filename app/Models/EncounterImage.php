<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EncounterImage extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'encounter_id', 'uploaded_by'];

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(PatientEncounter::class, 'encounter_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
