<?php

namespace App\Models;

use App\UserRole;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return ['role' => UserRole::class, 'is_active' => 'boolean', 'must_change_password' => 'boolean', 'password' => 'hashed', 'email_verified_at' => 'datetime', 'last_login_at' => 'datetime'];
    }

    public function isHod(): bool
    {
        return $this->role === UserRole::Hod;
    }

    public function isStudent(): bool
    {
        return $this->role === UserRole::Student;
    }

    public function isProfessor(): bool
    {
        return $this->role === UserRole::Professor;
    }

    public function isFaculty(): bool
    {
        return $this->isHod() || $this->isProfessor();
    }

    public function professor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'professor_id');
    }

    public function assignedStudents(): HasMany
    {
        return $this->hasMany(User::class, 'professor_id')->where('role', UserRole::Student);
    }

    public function supervises(User $student): bool
    {
        return $this->isProfessor() && $student->isStudent() && $student->professor_id === $this->id;
    }

    public function scopeVisibleStudents(Builder $query, User $viewer): void
    {
        $query->where('role', UserRole::Student)
            ->when($viewer->isProfessor(), fn (Builder $query) => $query->where('professor_id', $viewer->id))
            ->when($viewer->isStudent(), fn (Builder $query) => $query->whereKey($viewer->id));
    }

    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function encounters(): HasMany
    {
        return $this->hasMany(PatientEncounter::class, 'student_id');
    }

    public function hodReviews(): HasMany
    {
        return $this->hasMany(HodReview::class, 'hod_id');
    }

    public function requestedAiReviews(): HasMany
    {
        return $this->hasMany(AiReview::class, 'requested_by');
    }
}
