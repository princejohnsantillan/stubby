<?php

namespace App\Commands;

use App\Stubby;
use App\StubbyConfig;
use App\Enums\SpecialVariable;
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
        {--values= : Define variable values}
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
        $config = new StubbyConfig($this->option('config') ?? "stubs/config.json");

        /** @var string $stubKey */
        $stubKey = $this->argument('stub');

        /** @var string $filename */
        $filename = $this->argument('filename');

        $schema = $config->schemaOf($stubKey);

        if ($schema === null) {
            return $this->error('Invalid stub key');
        }

        $stub = data_get($schema, 'stub');

        if ($stub === null) {
            return $this->error('Stub is not defined');
        }

        try {
            $stubby = Stubby::stub($stub);
        } catch (FileNotFoundException) {
            return $this->error("Stub file not found");
        }

        $values = data_get($schema, 'values', []);

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

        /** @var string $variable */
        foreach ($stubby->interpretTokens()->pluck("variable") as $variable) {
            if (array_key_exists($variable, $values)) {
                continue;
            }

            if (SpecialVariable::valueExists($variable)) {
                continue;
            }

            $value = $this->ask("Provide a value for $variable");

            $values[$variable] = $value;
        }

        // File Path
        $filePath = Str::of(data_get($schema, 'file_path', ""))->trim()->rtrim("/")->toString();
        $filePath = $filePath !== "" ? $filePath."/" : "";

        // File Extenstion
        $fileExtension = Str::of(data_get($schema, 'file_extension', ""))->trim()->lower()->toString(); //TBD: improve extension case sensitivity

        // File Name
        $filename = Str::of($filename)->afterLast("/")->before($fileExtension)->trim()->toString();

        $filenameCase = Str::of(data_get($schema, 'filename_case', ""));

        $filenameMutation = StringMutation::find($filenameCase);

        $filename = $filenameMutation === null ? $filename : $filenameMutation->mutate($filename);

        // File Generate
        $stubby->generate($filePath.$filename.$fileExtension, $values);

        $this->info("Successfully generated {$filename}");
    }
}
