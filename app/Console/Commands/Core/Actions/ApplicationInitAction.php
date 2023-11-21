<?php

namespace App\Console\Commands\Core\Actions;

use Illuminate\Support\Str;
use function Laravel\Prompts\text;

class ApplicationInitAction extends AbstractInit
{
    public function registerApp(): self
    {
        $application = text(
            label: __("Зарегестрируйте директорию приложения"),
            placeholder: __("Допустим: application"),
            required: true,
            validate: fn (string $application) => match (true) {
                str($application)->length() < 2
                => __("Имя директории приложения не может быть короче 2 символов"),

                (Str::isMatch($this->pattern, $application))
                => __("Имя директории приложения не должно включать специальных символов"),

                default
                => null,
            },
            hint: __("Имя директории приложения должно быть без спец. символов"),
            default: 'application'
        );

        $this->application = str($application)->lower();

        return $this;
    }
}
