<?php

namespace App\Enums;

enum EducationLevel: int
{
    case NoEducation = 0;
    case CrecheOrECD = 1;
    case PrimaryLevel = 2; // Grade 1 - 7
    case SecondaryLevel = 3; // Form 1-4
    case ALevel = 4; // Form 5-6
    case College = 5;
    case Degree = 6;
    case PostGraduate = 7;
    case NotProvided = 8;

    public static function fromValue(?int $value): ?string
    {
        return match ($value) {
            self::NoEducation->value => 'No Education',
            self::CrecheOrECD->value => 'Creche or ECD',
            self::PrimaryLevel->value => 'Primary level (Grade 1 - 7)',
            self::SecondaryLevel->value => 'Secondary level (Form 1-4)',
            self::ALevel->value => 'A level (Form 5-6)',
            self::College->value => 'College',
            self::Degree->value => 'Degree',
            self::PostGraduate->value => 'Post Graduate',
            self::NotProvided->value => 'Not Provided',
            default => null,
        };
    }
}
