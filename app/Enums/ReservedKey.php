<?php
namespace App\Enums;

use Illuminate\Support\Str;

enum ReservedKey: string
{
    case FILENAME = "@FILENAME";
    case UUID = "@UUID";
    case ORDERED_UUID = "@ORDERED_UUID";

    public static function exists(string $key): bool
    {
        return static::tryFrom($key) !== null;
    }

    public function getValue(?array $meta =[]): ?string
    {
        return match ($this) {
            ReservedKey::FILENAME =>
                Str::of(data_get($meta, "FILENAME", ""))
                    ->afterLast("/")
                    ->before(".")
                    ->toString(),
            ReservedKey::UUID => Str::uuid(),
            ReservedKey::ORDERED_UUID => Str::orderedUuid(),
            default => null,
        };
    }
}
