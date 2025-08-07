<?php

namespace App\Enums;

enum Gender: int
{
    case Male = 1;
    case Female = 2;

    public static function fromValue(?int $value): ?string
    {
        return match ($value) {
            self::Male->value => 'Male',
            self::Female->value => 'Female',
            default => null,
        };
    }
}
