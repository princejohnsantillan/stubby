<?php

namespace App\Commands;

use App\Stubby;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class Make extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'make
        {type : Type of file stub to generate}
        {filename : Filename of generated content}
    ';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Generate stubs out of pre-defined stubs';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /** @var string $type */
        $type = $this->argument('type');

        /** @var string $filename */
        $filename = $this->argument('filename');

        $options = data_get(config('stubs'), $type);

        if ($options === null) {
            return $this->error('Invalid type');
        }

        $stub = data_get($options, 'stub');

        if ($stub === null) {
            return $this->error('Stub is not defined');
        }

        try {
            $stubby = Stubby::stub($stub);
        } catch (FileNotFoundException) {
            return $this->error("Stub file not found");
        }

        $values = data_get($options, 'defaults', []);

        foreach ($stubby->getTokens() as $token) {
            $key = Str::of($token)->between("{{ ", " }}")->toString();

            if (array_key_exists($key, $values)) {
                continue;
            }

            $value = $this->ask("Provide a value for {$token}");
            $values[$key] = $value;
        }

        $stubby->generate($filename, $values);

        $this->info("Successfully generated {$filename}");
    }
}
