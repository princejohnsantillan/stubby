<?php

namespace App;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class StubbyConfig
{
    private array $config;

    public function __construct(?string $configPath)
    {
        if (File::exists($configPath)) {
            $this->config = json_decode(File::get($configPath), true);
        } else {
            $this->config = config('stubs', []);
        }
    }

    public function options(): ?array
    {
        return data_get($this->config, 'options');
    }

    public function builds(): array
    {
        return data_get($this->config, 'builds', []);
    }

    public function descriptionOf(string $build): string
    {
        return data_get($this->builds(), $build.'.description', '');
    }

    public function schemaOf(string $build): ?array
    {
        return data_get($this->builds(), $build);
    }

    public function valuesOf(string $build): array
    {
        return data_get($this->schemaOf($build), 'values', []);
    }

    public function stubsOf(string $build): ?array
    {
        return data_get($this->schemaOf($build), 'stubs');
    }

    public function filePathFrom(array $fileConfig): string
    {
        $filePath = Str::of(data_get($fileConfig, 'file_path', ''))->trim()->rtrim('/')->toString();

        return $filePath !== '' ? $filePath.'/' : '';
    }

    public function fileExtensionFrom(array $fileConfig): string
    {
        return Str::of(data_get($fileConfig, 'file_extension', ''))->trim()->lower()->toString();
    }

    public function filenameCaseFrom(array $fileConfig): string
    {
        return data_get($fileConfig, 'filename_case', '');
    }

    public function filenameTemplateFrom(array $fileConfig): ?string
    {
        return data_get($fileConfig, 'filename');
    }
}
