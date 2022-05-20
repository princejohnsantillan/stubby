<?php
namespace App\Enums;

use BackedEnum;
use ValueError;

trait Helpers
{
    // Invokable
    public function __invoke()
    {
        return $this instanceof BackedEnum ? $this->value : $this->name;
    }

    public static function __callStatic($name, $args)
    {
        $cases = static::cases();

        foreach ($cases as $case) {
            if ($case->name === $name) {
                return $case instanceof BackedEnum ? $case->value : $case->name;
            }
        }

        throw new ValueError("$name is not a valid case for enum ".static::class);
    }

    // Names
    public static function names(): array
    {
        return array_column(static::cases(), 'name');
    }

    // Values
    public static function values(): array
    {
        $cases = static::cases();

        return isset($cases[0]) && $cases[0] instanceof BackedEnum
            ? array_column($cases, 'value')
            : array_column($cases, 'name');
    }

    // Options
    public static function options(): array
    {
        $cases = static::cases();

        return isset($cases[0]) && $cases[0] instanceof BackedEnum
            ? array_column($cases, 'value', 'name')
            : array_column($cases, 'name');
    }

    // Checks
    public static function exists($case): bool
    {
        return in_array($case, static::names());
    }

    public static function valueExists($case): bool
    {
        return in_array($case, static::values());
    }
}
