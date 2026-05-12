<?php

namespace App\Enums;

enum UserRole: string
{
    case Customer = 'customer';
    case Operator = 'operator';
    case Admin = 'admin';

    public function isOperatorOrAdmin(): bool
    {
        return in_array($this, [self::Operator, self::Admin], true);
    }
}
