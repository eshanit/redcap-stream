<?php

namespace App\Enums;

enum AHPMentalHealthManagement: string
{
    case R = 'Referred';
    case Rx = 'Treated';
    case NT = 'Not Treated';
    case NA = 'Not Applicable';

    public static function fromValue(?string $code): ?string
    {
        return match ($code) {
            self::R->name => self::R->value,
            self::Rx->name => self::Rx->value,
            self::NT->name => self::NT->value,
            self::NA->name => self::NA->value,
            default => null,
        };
    }
}
