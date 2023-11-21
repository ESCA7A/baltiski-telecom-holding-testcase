<?php

namespace App\Console\Commands\Core;

use App\Console\Commands\Core\Actions\ApplicationRegistration;
use Illuminate\Console\Command;

class Init extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Инициализация директории приложения. Создание домена и внесение соответствия путей в composer.json';

    /**
     * Execute the console command.
     */
    public function handle(ApplicationRegistration $action)
    {
        $action->run();
    }
}
