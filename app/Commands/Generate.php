<?php

namespace App\Commands;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use App\Stubby;

class Generate extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'generate
        {stub : Stub to make a file out of}
        {filename : Filename for to save generated content}
    ';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Generate a file from a given stub';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $stub = $this->argument('stub');
        $filename = $this->argument('filename');

        $this->info('Generating file from '. $stub);

        $stubby = Stubby::stub($stub);

        $values = [];

        foreach ($stubby->getTokens() as $token) {
            $key = Str::of($token)->between("{{ ", " }}")->toString();
            $value = $this->ask("Provide a value for {$token}");
            $values[$key] = $value;
        }

        $stubby->generate($filename, $values);

        $this->info("Successfully generated {$filename}");
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
