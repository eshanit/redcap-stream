<?php

namespace App\Enums;

enum AHPFacility: string
{
    case SIL = 'Silveira Hospital';
    case BIK = 'Bikita RHC';
    case CKK = 'Chikuku RHC';
    case MSH = 'Mashoko Hospital';
    case NDG = 'Ndanga Hospital';
    case MUS = 'Musiso Hospital';
    case BTA = 'Bota RHC';
    case HRV = 'Harava RHC';
    case CHI = 'Chiredzi Hospital';
    case CHK = 'Chikombedzi Hospital';

    public static function fromValue(?string $code): ?string
    {
        return match ($code) {
            self::SIL->name => self::SIL->value,
            self::BIK->name => self::BIK->value,
            self::CKK->name => self::CKK->value,
            self::MSH->name => self::MSH->value,
            self::NDG->name => self::NDG->value,
            self::MUS->name => self::MUS->value,
            self::BTA->name => self::BTA->value,
            self::HRV->name => self::HRV->value,
            self::CHI->name => self::CHI->value,
            self::CHK->name => self::CHK->value,
            default => null,
        };
    }
}
