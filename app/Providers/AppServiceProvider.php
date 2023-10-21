<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerMigrations();
    }

    private function registerMigrations(): void
    {
        $this->loadMigrationsFrom(get_files_path_by_prefix('migrations')->toArray());
    }
}
