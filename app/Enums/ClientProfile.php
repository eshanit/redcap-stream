<?php

namespace App\Enums;

enum ClientProfile: int
{
    case GeneralPopulation = 1;
    case SexWorker = 2;
    case MSM = 3; // Men having sex with men
    case WSW = 4; // Women having sex with women
    case PWUD = 5; // People who use drugs
    case PWID = 6; // People who inject drugs
    case Transgender = 7;
    case Other = 8;

    public static function fromValue(?int $value): ?string
    {
        return match ($value) {
            self::GeneralPopulation->value => 'General Population',
            self::SexWorker->value => 'Sex Worker',
            self::MSM->value => 'Men having sex with men (MSM)',
            self::WSW->value => 'Women having sex with women (WSW)',
            self::PWUD->value => 'People who use drugs (PWUD)',
            self::PWID->value => 'People who inject drugs (PWID)',
            self::Transgender->value => 'Transgender',
            self::Other->value => 'Other',
            default => null,
        };
    }
}
