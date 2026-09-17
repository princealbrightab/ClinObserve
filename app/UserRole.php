<?php

namespace App;

enum UserRole: string
{
    case Hod = 'hod';
    case Professor = 'professor';
    case Student = 'student';
}
