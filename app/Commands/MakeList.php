<?php

namespace App\Commands;

use App\Stubby;
use App\Enums\SpecialVariable;
use App\StubbyConfig;
use LaravelZero\Framework\Commands\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class MakeList extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'make:list
        {--config= : Define custom configutation}
    ';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Show list of available stubs';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $config = new StubbyConfig($this->option('config') ?? "stubs/config.json");

        $rows = [];
        foreach ($config->stubs() as $stub => $options) {
            $stubPath = data_get($options, "stub", "");

            try {
                $stubby = Stubby::stub($stubPath);
            } catch (FileNotFoundException) {
                continue;
            }

            $variables = $stubby->interpretTokens()
                ->pluck("variable")
                ->unique()
                ->reject(fn ($key) => in_array($key, SpecialVariable::values()))
                ->implode(", ");

            $rows[] = [
                $stub,
                $stubPath,
                $variables,
                data_get($options, "description", ""),
            ];
        }

        $this->table(['Stub', "Path", "Variables", "Description"], $rows);
    }
}
