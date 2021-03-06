<?php

namespace App\Commands;

use App\Enums\SpecialVariable;
use App\Stubby;
use App\StubbyConfig;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use LaravelZero\Framework\Commands\Command;

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
        $config = new StubbyConfig($this->option('config') ?? 'stubs/config.json');

        $rows = [];
        foreach ($config->builds() as $build => $buildConfig) {
            foreach ($config->stubsOf($build) ?? [] as $stub => $fileConfig) {
                try {
                    $stubby = Stubby::stub($stub);
                } catch (FileNotFoundException) {
                    continue;
                }

                $variables = $stubby->interpretTokens()
                    ->pluck('variable')
                    ->unique()
                    ->reject(fn ($key) => in_array($key, SpecialVariable::values()))
                    ->implode(', ');

                $rows[] = [
                    $build,
                    $stub,
                    $variables,
                    $config->descriptionOf($build),
                ];
            }
        }

        $this->table(['Build', 'Stub', 'Variables', 'Description'], $rows);
    }
}
