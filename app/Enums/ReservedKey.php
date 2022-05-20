<?php
namespace App\Enums;

use Illuminate\Support\Str;

enum ReservedKey: string
{
    use Helpers;

    case FILENAME = "@FILENAME";
    case UUID = "@UUID";
    case ORDERED_UUID = "@ORDERED_UUID";

    public function interpret(?array $meta =[]): ?string
    {
        return match ($this) {
            ReservedKey::FILENAME =>
                Str::of(data_get($meta, ReservedKey::FILENAME->value, ""))
                    ->afterLast("/")
                    ->before(".")
                    ->toString(),
            ReservedKey::UUID => Str::uuid(),
            ReservedKey::ORDERED_UUID => Str::orderedUuid(),
            default => null,
        };
    }
}
