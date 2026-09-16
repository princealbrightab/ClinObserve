<?php

namespace App\Services;

use App\Models\User;
use App\UserRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudentService
{
    public function save(array $data, User $hod, ?User $student = null): User
    {
        return DB::transaction(function () use ($data, $hod, $student): User {
            $new = $student === null;
            $student ??= new User;
            $student->fill(collect($data)->only(['name', 'email', 'password'])->all());
            if ($new) {
                $student->role = UserRole::Student;
                $student->created_by = $hod->id;
                $student->is_active = true;
                $student->must_change_password = true;
            }
            $student->save();
            $student->studentProfile()->updateOrCreate([], collect($data)->except(['name', 'email', 'password'])->all());

            return $student;
        });
    }

    public function resetCredentials(User $student, string $password): void
    {
        DB::transaction(function () use ($student, $password): void {
            $student->password = $password;
            $student->must_change_password = true;
            $student->remember_token = Str::random(60);
            $student->save();
            DB::table('sessions')->where('user_id', $student->id)->delete();
        });
    }
}
