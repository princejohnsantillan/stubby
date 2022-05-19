<?php

namespace App;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Stringable;

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

    public function getTokens(): array
    {
        return $this->content->matchAll('/{{ [a-zA-Z0-9_]+ }}/')->unique()->toArray();
    }

    public function generate(string $filename, array $values): bool
    {
        $content = $this->content;

        foreach ($values as $token => $value) {
            $content = Str::of($content->replace("{{ {$token} }}", $value));
        }

        if (Str::contains($filename, '/')) {
            File::ensureDirectoryExists(Str::beforeLast($filename, '/'));
        }

        return File::put($filename, $content);
    }
}
