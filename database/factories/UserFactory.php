<?php

namespace Database\Factories;

use App\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return ['name' => fake()->name(), 'email' => fake()->unique()->safeEmail(), 'email_verified_at' => now(), 'password' => static::$password ??= Hash::make('StudyAccess123!'), 'remember_token' => Str::random(10), 'role' => UserRole::Student, 'is_active' => true, 'must_change_password' => false];
    }

    public function hod(): static
    {
        return $this->state(fn () => ['role' => UserRole::Hod]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function professor(): static
    {
        return $this->state(fn () => ['role' => UserRole::Professor]);
    }

    public function student(): static
    {
        return $this->state(fn () => ['role' => UserRole::Student]);
    }

    public function temporaryPassword(): static
    {
        return $this->state(fn () => ['must_change_password' => true]);
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }
}
