<?php

namespace App\Console\Commands\Core\Actions;

abstract class AbstractInit
{
    /**
     * Название директории приложения
     */
    protected ?string $application = null;

    /**
     * Название домена
     */
    protected ?string $domain = null;

    /**
     * @see base_path()
     */
    readonly protected string $basePath;

    /**
     * Паттерн не корректных условий
     */
    readonly protected string $pattern;

    public function __construct()
    {
        $this->basePath = base_path();
        $this->pattern = '/[\W0-9]+/';
    }
}
