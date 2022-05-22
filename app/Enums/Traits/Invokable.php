<?php
namespace App\Enums\Traits;

use BackedEnum;
use ValueError;

trait Invokable
{
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
}
