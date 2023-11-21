<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

abstract class LayerGenerator extends Command
{
    /**
     * Домены
     */
    protected Collection $domainNames;

    /**
     * Имена модулей в слое
     */
    protected Collection $domainModules;

    /**
     * Все модули
     */
    protected Collection $allModules;

    /**
     * Имя установленного слоя
     */
    protected Collection $currentDomain;

    /**
     * Абсолютный путь к текущему домену
     */
    protected Collection $domainPath;

    /**
     * Абсолютный путь к текущему модулю
     */
    protected Collection $currentModulePath;

    /**
     * Паттерн не корректных условий
     */
    protected string $pattern = '/[\W0-9]+/';

    /**
     * Текущий модуль
     */
    protected Collection $currentModule;

    /**
     * Параметры
     */
    protected Collection $params;

    public function __construct()
    {
        $this->params = collect();
        parent::__construct();
    }

    protected function _execute(): self
    {
        $this->registerParameters();

        return $this;
    }

    /**
     * Регистрирует домен
     *
     * @var Collection $params [domain, modulename]
     */
    protected function validateDomain()
    {
        $domain = select(
            __("Выбери слой: "),
            $this->domainNames,
        );

        $domain = $this->domainNames->get($domain);

        $this->setCurrentDomain($domain);
    }

    /**
     * Регистрирует модуль
     */
    protected function validateModule()
    {
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
            $this->validateModule();
        }

        $this->setCurrentModule($moduleName);
    }

    /**
     * Регистрация параметров в текущей сущности для наследников
     */
    private function registerParameters(): void
    {
        $this->setDomainNames();

        $this->validateDomain();
        $this->setDomainModules($this->currentDomain->implode(''));
        $this->setCurrentDomainPath();

        $this->validateModule();
        $this->setCurrentModule($this->currentModule->implode(''));
        $this->setCurrentModulePath();
    }

    /**
     * Установка всех модулей в слое
     */
    private function setDomainModules(string $domain): void
    {
        $this->domainModules = get_modules_list($domain)->first();
    }

    /**
     * Установка имен слоев в приложении
     */
    private function setDomainNames(): void
    {
        $this->domainNames = get_domain_names();
    }

    /**
     * Установка имени текущего модуля
     */
    protected function setCurrentModule(string $modulename): void
    {
        $this->currentModule = collect($modulename);
    }

    /**
     * Установка имени текущего слоя
     */
    protected function setCurrentDomain(string $domain): void
    {
        $this->currentDomain = $this->domainNames->filter(fn ($name) => $name === $domain);
    }

    /**
     * Установка абсолютного пути к текущему домену
     */
    private function setCurrentDomainPath(): void
    {
        $this->domainPath = get_path_by_domain($this->currentDomain->implode(''));
    }

    /**
     * Установка абсолютного пути к текущему модулю
     */
    private function setCurrentModulePath(): void
    {
        $domainPath = get_path_by_domain($this->currentDomain->implode(''));
        $domainPath->push($this->currentModule->implode(''));

        $this->currentModulePath = collect($domainPath->implode('/'));
    }
}
