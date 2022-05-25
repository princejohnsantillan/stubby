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
        {build : Preconfigured build to generate}
        {filename : Filename of generated content}
        {--values= : Define variable values}
        {--config= : Define custom config}
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

        /** @var string $build */
        $build = $this->argument('build');

        /** @var string $filename */
        $inputFilename = $this->argument('filename');

        $schema = $config->schemaOf($build);

        if ($schema === null) {
            return $this->error('Invalid build name');
        }

        $stubs = $config->stubsOf($build);

        if ($stubs === null) {
            return $this->error('Stubs are not defined');
        }

        $values = $config->valuesOf($build);

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

        foreach ($stubs as $stub => $fileConfig) {
            try {
                $stubby = Stubby::stub($stub);
            } catch (FileNotFoundException) {
                return $this->error("Stub file not found: $stub");
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
            $filePath = $config->filePathFrom($fileConfig);

            // File Extenstion
            $fileExtension = $config->fileExtensionFrom($fileConfig);

            // File Name
            $filename = Str::of($inputFilename)->afterLast("/")->before($fileExtension)->trim()->toString();

            $filenameCase = $config->filenameCaseFrom($fileConfig);

            $filenameMutation = StringMutation::find($filenameCase);

            $filename = $filenameMutation === null ? $filename : $filenameMutation->mutate($filename);

            // Filename Template
            $filenameTemplate = $config->filenameTemplateFrom($fileConfig);

            $filename =  $filenameTemplate === null
                ? $filename
                : Str::of($filenameTemplate)->replace(SpecialVariable::FILENAME(), $filename)->toString();

            // File Generate
            $file = $filePath.$filename.$fileExtension;
            $stubby->generate($file, $values);

            $this->info("Successfully generated {$file}");
        }
    }
}
