<?php 
namespace App\Enum;

enum Status: string
{
    case Ukonczony = 'Ukonczony';
    case Rozpoczety = 'Rozpoczety';
    case Nierozpoczety = 'Nierozpoczety';
}
?>