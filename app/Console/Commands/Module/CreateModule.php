<?php

namespace App\Console\Commands\Module;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Throwable;
use function Laravel\Prompts\alert;
use function Laravel\Prompts\note;
use function Laravel\Prompts\text;
use function Laravel\Prompts\error;
use function Laravel\Prompts\select;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\multiselect;

class CreateModule extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:create-module';

    /**
     * The console command description.
     */
    protected $description = 'Создание модуля';

    private string $pattern = '/[\W0-9]+/';

    /**
     * Все пути доменов
     */
    private Collection $domainPaths;

    /**
     * Все имена доменов
     */
    private Collection $domainNames;

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
        $this->domainPaths = get_domain_paths();
        $this->domainNames = get_domain_names();
        $this->directories = config('module.suggested_directories', $this->directories);
        $this->defaultDirectories = config('module.default_directories', $this->defaultDirectories);

        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $params = $this->registerParams();
        $path = $this->generatePath($params);
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
        $newDirectories = $path->crossJoin($directories);
        $newDirectories = $newDirectories->map(fn ($directory) => implode('/', $directory));

        try {
            mkdir($path->implode(''));
            $newDirectories->each(fn ($newDirectory) => mkdir($newDirectory));
            note(__("Модуль успешно создан"));
        } catch (Throwable $e) {
            error($e->getMessage());
            exit();
        }
    }

    /**
     * Валидация параметров
     */
    private function registerParams(): Collection
    {
        $layer = select(
            __("В каком слое/домене создать модуль ?"),
            $this->domainNames,
        );

        /**
         * Валидация модуля
         */
        $moduleName = text(
            label: __("Имя модуля ?"),
            placeholder: __("Допустим: Products"),
            required: true,
            validate: fn (string $modulename) => match (true) {
                str($modulename)->length() < 3
                    => __("Имя модуля не может быть короче 3 символов"),

                (Str::ucfirst($modulename) !== $modulename)
                    => __("Имя модуля не может начинаться в нижнем регистре"),

                (Str::isMatch($this->pattern, $modulename))
                    => __("Имя модуля не должно включать специальных символов"),

                default
                    => null,
            },
            hint: __("Имя модуля должно начинаться с заглавной буквы и без специальных символов")
        );

        $confirm = confirm(__("Модуль называется {$moduleName} - все верно ?"));

        if ($confirm === false) {
            info(__("Пробуем заново!"));
            $this->registerParams();
        }

        return collect(['layer' => $layer, 'modulename' => $moduleName]);
    }

    private function generatePath(Collection $params): string
    {
        $layerKey = $params['layer'];
        $modulename = $params['modulename'];
        $layerName = $this->domainNames->get($layerKey);
        $path = get_path_by_domain($layerName);
        $path->push($modulename);

        if (module_is_exist($layerName, $modulename)) {
            alert(__("Такой модуль уже существует"));
            $this->handle();
        }

        return $path->implode('/');
    }
}
