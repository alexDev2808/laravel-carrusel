<?php

namespace App\Enums;

enum Role: string
{
    case User         = 'user';
    case Colaborador  = 'colaborador';
    case Admin        = 'admin';
    case Super        = 'super';
}
