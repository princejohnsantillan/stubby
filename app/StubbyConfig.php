<?php
namespace App;

use Illuminate\Support\Facades\File;

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

    public function options(): array
    {
        return data_get($this->config, "options", []);
    }

    public function stubs(): array
    {
        return data_get($this->config, "stubs", []);
    }

    public function schemaOf(string $stub): ?array
    {
        return data_get($this->stubs(), $stub);
    }
}
