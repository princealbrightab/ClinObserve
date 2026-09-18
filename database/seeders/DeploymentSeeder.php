<?php

namespace Database\Seeders;

use App\Models\User;
use App\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class DeploymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            if (User::where('role', UserRole::Hod)->exists()) {
                $this->command?->info('An HOD account already exists. No account or password was changed.');

                return;
            }

            $data = config('browser-maintenance.initial_hod');
            $data['email'] = strtolower(trim((string) ($data['email'] ?? '')));
            $validated = Validator::make($data, [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', Password::min(12)->letters()->numbers(), 'max:255'],
            ])->validate();

            $hod = new User($validated);
            $hod->role = UserRole::Hod;
            $hod->is_active = true;
            $hod->must_change_password = true;
            $hod->save();
            $this->command?->info('Initial HOD created. Sign in with the configured credentials and change the temporary password.');
        });
    }
}
