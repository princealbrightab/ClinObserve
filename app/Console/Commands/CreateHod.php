<?php

namespace App\Console\Commands;

use App\Models\User;
use App\UserRole;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateHod extends Command
{
    protected $signature = 'clinobserve:create-hod {email} {--name=}';

    protected $description = 'Create the initial HOD account with a securely prompted password';

    public function handle(): int
    {
        $email = strtolower((string) $this->argument('email'));
        $name = $this->option('name') ?: $this->ask('Full name');
        if (! $this->input->isInteractive()) {
            $this->error('Run this command interactively to enter a private password.');

            return self::FAILURE;
        }
        $password = $this->secret('Password (12+ characters, letters and numbers)');
        $validator = Validator::make(compact('email', 'name', 'password'), ['email' => ['required', 'email', 'max:255', 'unique:users'], 'name' => ['required', 'string', 'max:255'], 'password' => ['required', Password::min(12)->letters()->numbers(), 'max:255']]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

return self::FAILURE;
        }
        $user = new User(compact('email', 'name', 'password'));
        $user->role = UserRole::Hod;
        $user->is_active = true;
        $user->must_change_password = false;
        $user->save();
        $this->info('HOD account created.');

        return self::SUCCESS;
    }
}
