<?php

namespace App\Enums;

enum MaritalStatus: int
{
    case Married = 1;
    case NeverMarried = 2;
    case Widowed = 3;
    case DivorcedOrSeparated = 4;
    case LivingTogether = 5;
    case Minor = 6; // For children

    public static function fromValue(?int $value): ?string
    {
        return match ($value) {
            self::Married->value => 'Married',
            self::NeverMarried->value => 'Never Married',
            self::Widowed->value => 'Widowed',
            self::DivorcedOrSeparated->value => 'Divorced or Separated',
            self::LivingTogether->value => 'Living together',
            self::Minor->value => 'Minor (For children)',
            default => null,
        };
    }
}
