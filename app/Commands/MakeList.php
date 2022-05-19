<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

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
            $rows[] = [
                $stub,
                data_get($options, "stub", ""),
                data_get($options, "description", ""),
            ];
        }

        $this->table(['Stub', "Path", "Description"], $rows);
    }
}
