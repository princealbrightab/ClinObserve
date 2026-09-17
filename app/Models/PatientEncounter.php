<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PatientEncounter extends Model
{
    use HasFactory;

    public const FIELDS = ['summary', 'symptoms_observed', 'examination_findings', 'assessment', 'learning_notes'];

    protected $fillable = ['attended_at', 'summary', 'symptoms_observed', 'examination_findings', 'assessment', 'learning_notes'];

    protected function casts(): array
    {
        return ['attended_at' => 'datetime', 'locked_at' => 'datetime'];
    }

    public function scopeVisibleToProfessor(Builder $query, User $user): void
    {
        $query->when($user->isProfessor(), fn (Builder $query) => $query->whereHas('student', fn (Builder $query) => $query->visibleStudents($user)));
    }

    public function scopeForWorkspace(Builder $query, User $user): void
    {
        $query->visibleToProfessor($user)->when($user->isStudent(), fn (Builder $query) => $query->where('student_id', $user->id));
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(EncounterImage::class, 'encounter_id');
    }

    public function hodReviews(): HasMany
    {
        return $this->hasMany(HodReview::class, 'encounter_id');
    }

    public function aiReviews(): HasMany
    {
        return $this->hasMany(AiReview::class, 'encounter_id');
    }
}
