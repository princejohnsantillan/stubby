<?php

namespace App\Enums\Traits;

trait Names
{
    public static function names(): array
    {
        return array_column(static::cases(), 'name');
    }
}
