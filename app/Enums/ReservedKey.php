<?php
namespace App\Enums;

use App\Enums\Traits\Names;
use Illuminate\Support\Str;
use App\Enums\Traits\Checks;
use App\Enums\Traits\Values;
use App\Enums\Traits\Options;

enum ReservedKey: string
{
    use Names;
    use Values;
    use Options;
    use Checks;

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
