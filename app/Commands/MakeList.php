<?php

namespace App\Commands;

use App\Stubby;
use App\Enums\ReservedKey;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

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
        $configOption = $this->option('config') ?? "stubs/config.json";

        if (File::exists($configOption)) {
            $config = json_decode(File::get($configOption), true);
        } else {
            $config = config('stubs', []);
        }

        $rows = [];
        foreach ($config as $stub => $options) {
            $stubPath = data_get($options, "stub", "");

            try {
                $stubby = Stubby::stub($stubPath);
            } catch (FileNotFoundException) {
                continue;
            }

            $variables = $stubby->interpretTokens()
                ->pluck('key')
                ->unique()
                ->reject(fn ($key) => in_array($key, ReservedKey::values()))
                ->implode(", ");

            $rows[] = [
                $stub,
                $stubPath,
                $variables,
                data_get($options, "description", ""),
            ];
        }

        $this->table(['Stub', "Path", "Variables", "Description"], $rows);
    }
}
