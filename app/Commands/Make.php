<?php

namespace App\Commands;

use App\Stubby;
use App\Enums\ReservedKey;
use Illuminate\Support\Str;
use App\Enums\StringMutation;
use Illuminate\Support\Facades\File;
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
        {--config= : Define custom configutation}
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
        $configOption = $this->option('config') ?? "stubs/config.json";

        if (File::exists($configOption)) {
            $config = json_decode(File::get($configOption), true);
        } else {
            $config = config('stubs');
        }

        /** @var string $type */
        $stubKey = $this->argument('stub');

        /** @var string $filename */
        $filename = $this->argument('filename');

        $options = data_get($config, $stubKey);

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

        /** @var string $key */
        foreach ($stubby->interpretTokens()->pluck('key') as $key) {
            if (array_key_exists($key, $values)) {
                continue;
            }

            if (ReservedKey::valueExists($key)) {
                continue;
            }

            $value = $this->ask("Provide a value for $key");

            $values[$key] = $value;
        }

        // File Path
        $path = Str::of(data_get($options, 'path', ""))->trim()->rtrim("/")->toString();
        $path = $path !== "" ? $path."/" : "";

        // File Extenstion
        $extension = Str::of(data_get($options, 'extension', ""))->trim()->lower()->toString(); //TBD: improve extension case sensitivity

        // File Name
        $filename = Str::of($filename)->afterLast("/")->before($extension)->trim()->toString();

        $filenameCase = Str::of(data_get($options, 'filename_case', ""));

        $filenameMutation = StringMutation::find($filenameCase);

        $filename = $filenameMutation === null ? $filename : $filenameMutation->mutate($filename);


        // File Generate
        $stubby->generate($path.$filename.$extension, $values);

        $this->info("Successfully generated {$filename}");
    }
}
