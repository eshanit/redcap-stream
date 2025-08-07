<?php

namespace App\Enums;

enum AHPFamilyPlanning: int
{
    case ProgestogenOnlyPills = 1;
    case CombinedOralPills = 2;
    case EmergencyContraceptives = 3;
    case Injectables = 4;
    case Implants = 5;
    case IUCD = 6; // Intra-Uterine Devices
    case TubalLigation = 7;
    case Vasectomy = 8;
    case MaleCondoms = 9;
    case FemaleCondoms = 10;

    public static function fromValue(int $value): ?string
    {
        return match ($value) {
            self::ProgestogenOnlyPills->value => 'Progestogen only Pills',
            self::CombinedOralPills->value => 'Combined Oral Pills',
            self::EmergencyContraceptives->value => 'Emergency Contraceptives',
            self::Injectables->value => 'Injectables',
            self::Implants->value => 'Implants',
            self::IUCD->value => 'IUCD (Intra-Uterine Devices)',
            self::TubalLigation->value => 'Tubal Ligation',
            self::Vasectomy->value => 'Vasectomy',
            self::MaleCondoms->value => 'Male Condoms',
            self::FemaleCondoms->value => 'Female Condoms',
            default => null,
        };
    }

    // Add this method to handle multiple values
    public static function fromValues(array $values): array
    {
        return array_filter(array_map(function ($value) {
            return self::fromValue((int) $value);
        }, $values));
    }
}