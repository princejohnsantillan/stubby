<?php

namespace App\Commands;

use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use App\Stubby;
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
        {filename : Filename for to save generated content}
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

        $this->info("Generating file from {$stub}");

        try {
            $stubby = Stubby::stub($stub);
        } catch (FileNotFoundException) {
            return $this->error("Stub file not found");
        }

        $values = [];

        foreach ($stubby->getTokens() as $token) {
            $key = Str::of($token)->between("{{ ", " }}")->toString();
            $value = $this->ask("Provide a value for {$token}");
            $values[$key] = $value;
        }

        $stubby->generate($filename, $values);

        $this->info("Successfully generated {$filename}");
    }
}
