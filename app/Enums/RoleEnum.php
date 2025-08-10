<?php

namespace App\Enums;

enum RoleEnum: string
{
    case STUDENT = 'student';
    case ACADEMIC = 'academic';
    case ADMIN = 'admin';
}
