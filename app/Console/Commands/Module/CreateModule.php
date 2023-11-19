<?php

namespace App\Console\Commands\Module;

use App\Console\Commands\LayerGenerator;
use Illuminate\Support\Collection;
use Throwable;
use function Laravel\Prompts\alert;
use function Laravel\Prompts\note;
use function Laravel\Prompts\error;
use function Laravel\Prompts\multiselect;

class CreateModule extends LayerGenerator
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:create-module';

    /**
     * The console command description.
     */
    protected $description = 'Создание модуля';

    private array $directories = [
        'Actions',
        'Controllers',
        'routes',
        'Admin',
        'database',
        'DTO',
        'Enums',
        'Models',
        'Requests',
    ];

    private array $defaultDirectories = [
        'Actions',
        'Controllers',
        'routes',
    ];

    public function __construct()
    {
        $this->directories = config('module.suggested_directories', $this->directories);
        $this->defaultDirectories = config('module.default_directories', $this->defaultDirectories);

        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->_execute();
        $path = $this->generatePath($this->params);
        $this->generateModule($path);
    }

    private function generateModule(string $path): void
    {
        $directories = multiselect(
            label: __("Создать директории ?"),
            options: $this->directories,
            default: $this->defaultDirectories,
        );

        $path = collect($path);
        $createDirectories = $path->crossJoin($directories);
        $createDirectories = $createDirectories->map(fn ($directory) => implode('/', $directory));

        try {
            mkdir($path->implode(''));

            if ($createDirectories->count() > 0) {
                $createDirectories->each(fn ($newDirectory) => mkdir($newDirectory));
            }

            note(__("Модуль успешно создан"));
        } catch (Throwable $e) {
            error($e->getMessage());
            exit();
        }
    }

    private function generatePath(Collection $params): string
    {
        $layerName = $params['domain'];
        $modulename = $params['modulename'];
        $path = get_path_by_domain($layerName);
        $path->push($modulename);

        if (module_is_exist($layerName, $modulename)) {
            alert(__("Такой модуль уже существует"));
            $this->handle();
        }

        return $path->implode('/');
    }
}
