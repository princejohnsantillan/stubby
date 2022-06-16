<?php

namespace App\Enums;

use Illuminate\Support\Str;

enum StringMutation: string
{
    case CAMEL = 'camel';
    case HEADLINE = 'headline';
    case KEBAB = 'kebab';
    case LOWER_FIRST = 'lcfirst';
    case LOWER = 'lower';
    case PLURAL = 'plural';
    case PLURAL_STUDLY = 'pluralstudly';
    case REVERSE = 'reverse';
    case SINGULAR = 'singular';
    case SLUG = 'slug';
    case SNAKE = 'snake';
    case STUDLY = 'studly';
    case TITLE = 'title';
    case UPPER_FIRST = 'ucfirst';
    case UPPER = 'upper';

    public static function find(string $name): ?static
    {
        return StringMutation::tryFrom(Str::of($name)->remove(' ')->lower()->toString());
    }

    public function mutate(string $value): string
    {
        return match ($this) {
            StringMutation::CAMEL         => Str::camel($value),
            StringMutation::HEADLINE      => Str::headline($value),
            StringMutation::KEBAB         => Str::kebab($value),
            StringMutation::LOWER_FIRST   => Str::lcfirst($value),
            StringMutation::LOWER         => Str::lower($value),
            StringMutation::PLURAL        => Str::plural($value),
            StringMutation::PLURAL_STUDLY => Str::pluralStudly($value),
            StringMutation::REVERSE       => Str::reverse($value),
            StringMutation::SINGULAR      => Str::singular($value),
            StringMutation::SLUG          => Str::slug($value),
            StringMutation::SNAKE         => Str::snake($value),
            StringMutation::STUDLY        => Str::studly($value),
            StringMutation::TITLE         => Str::title($value),
            StringMutation::UPPER_FIRST   => Str::ucfirst($value),
            StringMutation::UPPER         => Str::upper($value),
            default                       => $value,
        };
    }
}
