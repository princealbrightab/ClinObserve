<?php

namespace Database\Seeders;

use App\Models\User;
use App\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $emails = ['hod@clinobserve.test', 'professor@clinobserve.test', 'student@clinobserve.test'];
            if (User::whereIn('email', $emails)->exists()) {
                $this->command?->info('Demo accounts already exist; seeding skipped to preserve records.');

                return;
            }

            $hod = User::create([
                'name' => 'HOD Admin',
                'email' => 'hod@clinobserve.test',
                'password' => Hash::make('Hod@12345'),
            ]);
            $hod->role = UserRole::Hod;
            $hod->is_active = true;
            $hod->must_change_password = false;
            $hod->save();

            $professor = User::create([
                'name' => 'Professor Admin',
                'email' => 'professor@clinobserve.test',
                'password' => Hash::make('Professor@12345'),
            ]);
            $professor->role = UserRole::Professor;
            $professor->created_by = $hod->id;
            $professor->is_active = true;
            $professor->must_change_password = false;
            $professor->save();

            $student = User::create([
                'name' => 'Student User',
                'email' => 'student@clinobserve.test',
                'password' => Hash::make('Student@12345'),
            ]);
            $student->role = UserRole::Student;
            $student->created_by = $hod->id;
            $student->professor_id = $professor->id;
            $student->is_active = true;
            $student->must_change_password = false;
            $student->save();
        });
    }
}
