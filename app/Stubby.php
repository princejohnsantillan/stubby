<?php

namespace App;

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

    public function getContent(): string
    {
        return $this->content->toString();
    }

    public function getTokens(): Collection
    {
        return $this->content->matchAll('/{{[a-zA-Z0-9 _|]+}}/')->unique();
    }

    public function interpretTokens(): Collection
    {
        return $this->getTokens()->mapWithKeys(
            function (string $token) {
                $tokenMeta = Str::of($token)->between("{{", "}}")->explode("|");

                /** @var string $key */
                $key = Str::of($tokenMeta->get(0))->remove(" ")->lower()->toString();

                /** @var string $mutation */
                $mutation = Str::of($tokenMeta->get(1, ""))->remove(" ")->lower()->toString();

                return [
                    $key => [
                        "token" => $token,
                        "mutation" => StringMutation::tryFrom($mutation)
                    ]
                ];
            }
        );
    }

    public function generate(string $filename, Collection|array $values): bool
    {
        $content = $this->content;
        $tokensMeta = $this->interpretTokens();

        foreach ($values as $key => $value) {
            $sanitizedKey = Str::of($key)->remove(" ")->lower()->toString();
            $meta = $tokensMeta->only($sanitizedKey);

            if ($meta->isEmpty()) {
                continue;
            }

            /** @var string $token */
            $token = $meta->pluck("token")->get(0);

            /** @var StringMutation|null $mutation */
            $mutation = $meta->pluck("mutation")->get(0);

            $value = $mutation === null ? $value : $mutation->mutate($value);

            $content = Str::of($content->replace($token, $value));
        }

        if (Str::contains($filename, '/')) {
            File::ensureDirectoryExists(Str::beforeLast($filename, '/'));
        }

        return File::put($filename, $content);
    }
}
