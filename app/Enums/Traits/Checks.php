<?php
namespace App\Enums\Traits;

use BackedEnum;

trait Checks
{
    public static function exists($case): bool
    {
        return in_array($case, array_column(static::cases(), 'name'));
    }

    public static function valueExists($value): bool
    {
        $cases = static::cases();

        $values = isset($cases[0]) && $cases[0] instanceof BackedEnum
            ? array_column($cases, 'value')
            : array_column($cases, 'name');

        return in_array($value, $values);
    }
}
