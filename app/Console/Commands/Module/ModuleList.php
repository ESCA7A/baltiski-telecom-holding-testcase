<?php

namespace App\Console\Commands\Module;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class ModuleList extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:module:list';

    /**
     * The console command description.
     */
    protected $description = 'Список модулей';

    /**
     * Список модулей
     */
    private Collection $modulesList;

    public function __construct()
    {
        $this->modulesList = get_modules_list();
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dd($this->modulesList->toArray());
    }
}
