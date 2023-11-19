<?php

namespace App\Console\Commands;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use function Laravel\Prompts\alert;
use function Laravel\Prompts\note;
use function Laravel\Prompts\text;
use function Laravel\Prompts\error;
use function Laravel\Prompts\select;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\multiselect;

class Make extends LayerGenerator
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:make {stub}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создание шаблона';

    /**
     * Название стаба
     */
    protected ?string $stubTitle = 'controller';

    protected $stubPath = '';

    /**
     * Имя стаба
     */
    protected Collection $stub;

    protected array $replace = [
        '{{ domain }}',
        '{{ module }}',
        '{{ namespace }}',
        '{{ class }}',
        '{{ command }}',
        '{{ rootNamespace }}',
        '{{ factoryNamespace }}',
        '{{ factory }}',
        '{{ subject }}',
        '{{ view }}',
        '{{ table }}',
        '{{ namespacedModel }}',
        '{{ modelVariable }}',
        '{{ namespacedUserModel }}',
        '{{ user }}',
        '{{ model }}',
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->_execute();
        $name = $this->getStub();
        dd($this->buildClass($name));
    }

    /**
     * Регистрирует модуль
     */
    protected function validateModule()
    {
        /**
         * Валидация модуля
         */
        $modulename = select(
            label: __("Выбери модуль: "),
            options: $this->domainModules,
        );

        $domain = $this->domainModules->get($modulename);

        $this->params->put('modulename', $domain);
        $this->setCurrentModule($modulename);
    }

    protected function getStub()
    {
        return $this->resolveStubPath($this->stubTitle);
    }

    protected function resolveStubPath($stub)
    {
        $this->stubPath = get_files_path_by_prefix('stubs', 'app')
            ->filter(fn ($path) => Str::isMatch("/{$stub}/", $path))->first();

        return $this->stubPath;
    }

    protected function buildClass($name)
    {
        $factory = class_basename(Str::ucfirst(str_replace('Factory', '', $name)));

//        $namespaceModel = $this->option('model')
//            ? $this->qualifyModel($this->option('model'))
//            : $this->qualifyModel($this->guessModelName($name));
//
//        $model = class_basename($namespaceModel);
        dd($this->currentModule);
        $namespace = $this->getNamespace(
            Str::replaceFirst($this->rootNamespace(), 'Database\\Factories\\', $this->qualifyClass($this->getNameInput()))
        );
        dd($namespace);
        $replace = [
            '{{ factoryNamespace }}' => $namespace,
            'NamespacedDummyModel' => $namespaceModel,
            '{{ namespacedModel }}' => $namespaceModel,
            '{{namespacedModel}}' => $namespaceModel,
            'DummyModel' => $model,
            '{{ model }}' => $model,
            '{{model}}' => $model,
            '{{ factory }}' => $factory,
            '{{factory}}' => $factory,
        ];

        return str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );
    }

    protected function rootNamespace()
    {
        return $this->currentDomain->first() . '\\';
    }
}
