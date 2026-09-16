<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Patient extends Model
{
    use HasFactory;

    public const CLINICAL_FIELDS = ['chief_complaint', 'presenting_symptoms', 'history_of_present_illness', 'past_medical_history', 'family_history', 'medication_history', 'allergy_history', 'social_history', 'examination_findings', 'working_diagnosis', 'confirmed_diagnosis', 'investigations', 'management_notes', 'clinical_notes'];

    protected $guarded = ['id', 'case_number', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return ['admission_date' => 'date', 'additional_context' => 'array'];
    }

    protected static function booted(): void
    {
        static::creating(function (Patient $patient): void {
            $patient->case_number = 'CASE-'.Str::ulid();
        });
    }

    public function encounters(): HasMany
    {
        return $this->hasMany(PatientEncounter::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
