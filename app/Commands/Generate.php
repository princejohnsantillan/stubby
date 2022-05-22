<?php

namespace App\Commands;

use App\Stubby;
use App\Enums\ReservedKey;
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

        /** @var string $key */
        foreach ($stubby->interpretTokens()->pluck('key') as $key) {
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
