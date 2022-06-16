<?php

namespace App\Enums;

use App\Enums\Traits\Checks;
use App\Enums\Traits\Invokable;
use App\Enums\Traits\Names;
use App\Enums\Traits\Options;
use App\Enums\Traits\Values;
use Illuminate\Support\Str;

/**
 * @method static self FILENAME()
 * @method static self UUID()
 * @method static self ORDERED_UUID()
 */
enum SpecialVariable: string
{
    use Invokable;
    use Names;
    use Values;
    use Options;
    use Checks;

    case FILENAME = '@FILENAME';
    case UUID = '@UUID';
    case ORDERED_UUID = '@ORDERED_UUID';

    public function interpret(?array $meta = []): ?string
    {
        return match ($this) {
            SpecialVariable::FILENAME => Str::of(data_get($meta, SpecialVariable::FILENAME(), ''))
                    ->afterLast('/')
                    ->before('.')
                    ->toString(),
            SpecialVariable::UUID         => Str::uuid(),
            SpecialVariable::ORDERED_UUID => Str::orderedUuid(),
            default                       => null,
        };
    }
}
