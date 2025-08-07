<?php

namespace App\Enums;

enum ANCFirstBooking: int
{
    case Yes = 1;
    case No = 0;

    public static function fromValue(?int $value): ?string
    {
        return match ($value) {
            self::Yes->value => 'Yes',
            self::No->value => 'No',
            default => null,
        };
    }
}
