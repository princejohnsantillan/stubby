<?php

namespace App\Commands;

use App\Stubby;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class MakeList extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'make:list';

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
        $rows = [];
        foreach (config('stubs', []) as $stub => $options) {
            $stubPath = data_get($options, "stub", "");

            try {
                $stubby = Stubby::stub($stubPath);
            } catch (FileNotFoundException) {
                continue;
            }

            $variables = collect($stubby->getTokens())
                    ->map(fn ($token) => Str::between($token, '{{ ', ' }}'))
                    ->implode(', ');

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
