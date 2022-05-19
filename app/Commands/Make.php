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
        {stub : Preconfigured stub to generate}
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
        $stubKey = $this->argument('stub');

        /** @var string $filename */
        $filename = $this->argument('filename');

        $options = data_get(config('stubs'), $stubKey);

        if ($options === null) {
            return $this->error('Invalid stub key');
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

        foreach ($stubby->interpretTokens()->pluck('key') as $key) {
            if (array_key_exists($key, $values)) {
                continue;
            }

            $value = $this->ask("Provide a value for $key");

            $values[$key] = $value;
        }

        $path = data_get($options, 'path', "");

        if ($path !== "") {
            $path = Str::beforeLast($path, "/")."/";
        }

        $extension = data_get($options, 'extension', "");

        $filename = Str::contains($filename, "/") ? $filename : $path.$filename;

        $stubby->generate(Str::before($filename, $extension).$extension, $values);

        $this->info("Successfully generated {$filename}");
    }
}
