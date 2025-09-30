<?php

namespace App\Enum;

enum Permission: string
{
    // Customer permissions
    case CUSTOMER_VIEW = 'CUSTOMER_VIEW';
    case CUSTOMER_DETAIL = 'CUSTOMER_DETAIL';
    case CUSTOMER_EDIT = 'CUSTOMER_EDIT';
    case CUSTOMER_DELETE = 'CUSTOMER_DELETE';
    case CUSTOMER_CREATE = 'CUSTOMER_CREATE';

    // Course permissions
    case COURSE_VIEW = 'COURSE_VIEW';
    case COURSE_CREATE = 'COURSE_CREATE';
    case COURSE_EDIT = 'COURSE_EDIT';
    case COURSE_DELETE = 'COURSE_DELETE';

    // Theory permissions
    case THEORY_VIEW = 'THEORY_VIEW';
    case THEORY_CREATE = 'THEORY_CREATE';
    case THEORY_EDIT = 'THEORY_EDIT';
    case THEORY_DELETE = 'THEORY_DELETE';

    case CAR_VIEW = 'CAR_VIEW';
    case CAR_CREATE = 'CAR_CREATE';
    case CAR_EDIT = 'CAR_EDIT';
    case CAR_DELETE = 'CAR_DELETE';

    case FAQ_MANAGMENT = 'FAQ_MANAGMENT';

    case ADMIN_PANEL = 'ADMIN_PANEL';
    case USER_MANAGEMENT = 'USER_MANAGEMENT';
    case ROLE_MANAGEMENT = 'ROLE_MANAGEMENT';

    case EMPLOYEE_THEORY = 'EMPLOYEE_THEORY';
    case EMPLOYEE_COURSE = 'EMPLOYEE_COURSE';
    case CUSTOMER = 'CUSTOMER';
    case ADMIN = 'ADMIN';
    case CALENDARY_EMPLOYEES_VIEW = 'CALENDARY_EMPLOYEES_VIEW';

    public function getLabel(): string
    {
        return match ($this) {
            self::CUSTOMER_VIEW => 'Wyświetlanie klientów',
            self::CUSTOMER_DETAIL => 'Szczegóły klienta',
            self::CUSTOMER_EDIT => 'Edycja klientów',
            self::CUSTOMER_DELETE => 'Usuwanie klientów',
            self::CUSTOMER_CREATE => 'Dodawanie klientów',

            self::COURSE_VIEW => 'Wyświetlanie kursów',
            self::COURSE_CREATE => 'Dodawanie kursów',
            self::COURSE_EDIT => 'Edycja kursów',
            self::COURSE_DELETE => 'Usuwanie kursów',

            self::THEORY_VIEW => 'Wyświetlanie teorii',
            self::THEORY_CREATE => 'Dodawanie teorii',
            self::THEORY_EDIT => 'Edycja teorii',
            self::THEORY_DELETE => 'Usuwanie teorii',

            self::CAR_VIEW => 'Wyświetlanie pojazdów',
            self::CAR_CREATE => 'Dodawanie pojazdów',
            self::CAR_EDIT => 'Edycja pojazdów',
            self::CAR_DELETE => 'Usuwanie pojazdów',

            self::FAQ_MANAGMENT => 'Zarządzanie FAQ',

            self::ADMIN_PANEL => 'Panel administracyjny',
            self::USER_MANAGEMENT => 'Zarządzanie użytkownikami',
            self::ROLE_MANAGEMENT => 'Zarządzanie rolami',

            self::EMPLOYEE_THEORY => 'Instruktor teorii',
            self::EMPLOYEE_COURSE => 'Instruktor jazdy',
            self::CUSTOMER => 'Kursant',
            self::ADMIN => 'Administrator',
            self::CALENDARY_EMPLOYEES_VIEW => 'Podgląd kalendarza pracowników',
        };
    }

    public function getCategory(): string
    {
        return match ($this) {
            self::CUSTOMER_VIEW, self::CUSTOMER_DETAIL, self::CUSTOMER_EDIT,
            self::CUSTOMER_DELETE, self::CUSTOMER_CREATE => 'Klienci',

            self::COURSE_VIEW, self::COURSE_CREATE, self::COURSE_EDIT,
            self::COURSE_DELETE => 'Kursy',

            self::THEORY_VIEW, self::THEORY_CREATE, self::THEORY_EDIT,
            self::THEORY_DELETE => 'Teoria',

            self::CAR_VIEW, self::CAR_CREATE, self::CAR_EDIT,
            self::CAR_DELETE => 'Pojazdy',

            self::FAQ_MANAGMENT => 'Zawartość',

            self::ADMIN_PANEL, self::USER_MANAGEMENT,
            self::ROLE_MANAGEMENT, self::ADMIN => 'Administracja',

            self::EMPLOYEE_THEORY, self::EMPLOYEE_COURSE,
            self::CUSTOMER => 'Role użytkowników',

            self::CALENDARY_EMPLOYEES_VIEW => 'Kalendarz',
        };
    }
}
