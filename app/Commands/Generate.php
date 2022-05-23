<?php

namespace App\Commands;

use App\Stubby;
use App\Enums\ReservedKey;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class Generate extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = "generate
        {stub : Stub to make a file out of}
        {filename : Filename for generated content}
        {--values= : Define variable values}
    ";

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = "Generate a file from a given stub";

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $stub = $this->argument("stub");
        $filename = $this->argument("filename");

        try {
            $stubby = Stubby::stub($stub);
        } catch (FileNotFoundException) {
            return $this->error("Stub file not found");
        }

        $values = [];

        foreach (Str::of($this->option("values") ?? "")->explode(",")->filter() as $value) {
            $parts = Str::of($value)->explode(":")->filter();

            /** @var string $key */
            $key = $parts->get(0);

            /** @var string $value */
            $value = $parts->get(1);

            if ($key === null || $value === null) {
                continue;
            }

            $values[$key] = $value;
        }


        /** @var string $key */
        foreach ($stubby->interpretTokens()->pluck('key') as $key) {
            if (array_key_exists($key, $values)) {
                continue;
            }

            if (ReservedKey::valueExists($key)) {
                continue;
            }

            $value = $this->ask("Provide a value for \"$key\"");

            $values[$key] = $value;
        }

        $stubby->generate($filename, $values);

        $this->info("Successfully generated {$filename}");
    }
}
