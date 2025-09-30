<?php

namespace App\Enum;

enum Role: int
{
    case ADMIN = 1;
    case EMPLOYEE_COURSE = 2;
    case EMPLOYEE_THEORY = 3;
    case CUSTOMER = 4;
}
