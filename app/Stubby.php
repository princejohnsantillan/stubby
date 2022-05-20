<?php

namespace App;

use App\Enums\ReservedKey;
use Illuminate\Support\Str;
use App\Enums\StringMutation;
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;
use Illuminate\Support\Facades\File;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class Stubby
{
    private Stringable $content;

    public function __construct(private string $stub)
    {
        if (!File::exists($stub)) {
            throw new FileNotFoundException;
        }

        $this->content = Str::of(File::get($stub));
    }

    public static function stub(string $stub):self
    {
        return new static($stub);
    }

    public function getRawContent(): string
    {
        return $this->content->toString();
    }

    public function getRawTokens(): Collection
    {
        return $this->content->matchAll('/{{[a-zA-Z0-9 _|@]+}}/')->unique();
    }

    public function interpretTokens(): Collection
    {
        return $this->getRawTokens()->mapWithKeys(
            function (string $token) {
                $tokenParts = Str::of($token)->between("{{", "}}")->explode("|");

                /** @var string $key */
                $key = Str::of($tokenParts->get(0))->remove(" ")->toString();

                /** @var string $mutation */
                $mutation = Str::of($tokenParts->get(1, ""));

                return [
                    $token => [
                        "key" => $key,
                        "mutation" => StringMutation::find($mutation)
                    ]
                ];
            }
        );
    }

    public function generate(string $filename, Collection|array $values): bool
    {
        $content = $this->content;
        $tokens = $this->interpretTokens();

        foreach ($tokens as $token => $meta) {
            $key = $meta["key"];

            $value = ReservedKey::tryFrom($key)?->getValue(["FILENAME" => $filename]);

            /** @var string $value */
            $value = $value ?? data_get($values, $key);

            if ($value === null) {
                continue;
            }

            /** @var StringMutation|null $mutation */
            $mutation = data_get($meta, 'mutation');

            $value = $mutation === null ? $value : $mutation->mutate($value);

            $content = Str::of($content->replace($token, $value));
        }

        if (Str::contains($filename, '/')) {
            File::ensureDirectoryExists(Str::beforeLast($filename, '/'));
        }

        return File::put($filename, $content);
    }
}
