<?php

namespace App\Enums;

enum AHPMentalHealth: string
{
    case ND = 'No Mental Health Disorders';
    case D = 'Depression';
    case A = 'Anxiety';
    case SA = 'Substance Misuse';
    case O = 'Other';

    public static function fromValue(?string $code): ?string
    {
        return match ($code) {
            self::ND->name => self::ND->value,
            self::D->name => self::D->value,
            self::A->name => self::A->value,
            self::SA->name => self::SA->value,
            self::O->name => self::O->value,
            default => null,
        };
    }
}
