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

class CreateModule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-module';

    /**
     * The console command description.
     *
     * @var string
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

    /**
     * Лист всех модулей
     */
    private Collection $modulesList;

    /**
     * Директория модуля
     */
    private Collection $module;

    private array $generate = [
        'Actions',
        'database',
        'DTO',
        'Enums',
        'Models',
        'Requests',
    ];

    public function __construct()
    {
        $this->domainPaths = get_domain_paths();
        $this->domainNames = get_domain_names();
        $this->modulesList = get_modules_list();
        $this->module = collect($this->generate);

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
        $path = collect($path);
        $newDirectories = $path->crossJoin($this->generate);
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
            'В каком слое/домене создать модуль ?',
            $this->domainNames,
        );

        /**
         * Валидация модуля
         */
        $moduleName = text(
            label: 'Имя модуля ?',
            placeholder: 'Допустим: Products',
            required: true,
            validate: fn (string $modulename) => match (true) {
                str($modulename)->length() < 3
                    => 'Имя модуля не может быть короче 3 символов',

                (Str::ucfirst($modulename) !== $modulename)
                    => 'Имя модуля не может начинаться в нижнем регистре',

                (Str::isMatch($this->pattern, $modulename))
                    => 'Имя модуля не должно включать специальных символов',

                default
                    => null,
            },
            hint: 'Имя модуля должно начинаться с заглавной буквы и без специальных символов'
        );

        $confirm = confirm("Модуль называется {$moduleName} - все верно ?");

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
