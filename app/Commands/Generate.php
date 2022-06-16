<?php

namespace App\Commands;

use App\Enums\SpecialVariable;
use App\Stubby;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;

class Generate extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'generate
        {stub : Stub to make a file out of}
        {filename : Filename for generated content}
        {--values= : Define variable values}
    ';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Generate a file from a given stub';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $stub = $this->argument('stub');
        $filename = $this->argument('filename');

        try {
            $stubby = Stubby::stub($stub);
        } catch (FileNotFoundException) {
            return $this->error('Stub file not found');
        }

        $values = [];

        foreach (Str::of($this->option('values') ?? '')->explode(',')->filter() as $value) {
            $parts = Str::of($value)->explode(':')->filter();

            /** @var string $key */
            $key = $parts->get(0);

            /** @var string $value */
            $value = $parts->get(1);

            if ($key === null || $value === null) {
                continue;
            }

            $values[$key] = $value;
        }

        /** @var string $variable */
        foreach ($stubby->interpretTokens()->pluck('variable') as $variable) {
            if (array_key_exists($variable, $values)) {
                continue;
            }

            if (SpecialVariable::valueExists($variable)) {
                continue;
            }

            $value = $this->ask("Provide a value for \"$variable\"");

            $values[$variable] = $value;
        }

        $stubby->generate($filename, $values);

        $this->info("Successfully generated {$filename}");
    }
}
