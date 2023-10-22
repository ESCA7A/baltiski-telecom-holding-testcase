<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Str;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->loadCommands();
        require base_path('routes/console.php');
    }

    private function loadCommands(): void
    {
        $paths = get_dirs_path_by_prefix('Commands', 'app');
        $paths = collect(Str::replace('.', '', $paths))->unique();
        $paths->each(fn ($item) => $this->load($item));
    }
}
