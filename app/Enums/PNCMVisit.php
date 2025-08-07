<?php

namespace App\Enums;

enum PNCMVisit: int
{
    //
    case Day1 = 1;
    case Day3 = 2;
    case Day7 = 3;
    case SixWeeks = 4;
    case TenWeeks = 5;
    case FourteenWeeks = 6;
    case FiveMonths = 7;
    case SixMonths = 8;
    case SevenMonths = 9;
    case EightMonths = 10;
    case NineMonths = 11;
    case TenMonths = 12;
    case ElevenMonths = 13;
    case TwelveMonths = 14;
    case ThirteenMonths = 15;
    case FourteenMonths = 16;
    case FifteenMonths = 17;
    case SixteenMonths = 18;
    case SeventeenMonths = 19;
    case EighteenMonths = 20;
    case NineteenMonths = 21;
    case TwentyMonths = 22;
    case TwentyOneMonths = 23;
    case TwentyTwoMonths = 24;
    case TwentyThreeMonths = 25;
    case TwentyFourMonths = 26;

    public static function fromValue(?int $value): ?string
    {
        return match ($value) {
            self::Day1->value => 'Day 1',
            self::Day3->value => 'Day 3',
            self::Day7->value => 'Day 7',
            self::SixWeeks->value => '6 Weeks',
            self::TenWeeks->value => '10 Weeks',
            self::FourteenWeeks->value => '14 Weeks',
            self::FiveMonths->value => '5 Months',
            self::SixMonths->value => '6 Months',
            self::SevenMonths->value => '7 Months',
            self::EightMonths->value => '8 Months',
            self::NineMonths->value => '9 Months',
            self::TenMonths->value => '10 Months',
            self::ElevenMonths->value => '11 Months',
            self::TwelveMonths->value => '12 Months',
            self::ThirteenMonths->value => '13 Months',
            self::FourteenMonths->value => '14 Months',
            self::FifteenMonths->value => '15 Months',
            self::SixteenMonths->value => '16 Months',
            self::SeventeenMonths->value => '17 Months',
            self::EighteenMonths->value => '18 Months',
            self::NineteenMonths->value => '19 Months',
            self::TwentyMonths->value => '20 Months',
            self::TwentyOneMonths->value => '21 Months',
            self::TwentyTwoMonths->value => '22 Months',
            self::TwentyThreeMonths->value => '23 Months',
            self::TwentyFourMonths->value => '24 Months',
            default => null,
        };
    }
}
