<?php

namespace App\Console\Commands\Core\Actions;

use Illuminate\Support\Str;
use function Laravel\Prompts\text;

class DomainInitAction extends ApplicationInitAction
{
    public function registerDomain(): self
    {
        $domain = text(
            label: __("Зарегестрируйте имя домена"),
            placeholder: __("Допустим: Domain"),
            required: true,
            validate: fn (string $domain) => match (true) {
                str($domain)->length() < 3
                => __("Имя домена не может быть короче 3 символов"),

                (Str::isMatch($this->pattern, $domain))
                => __("Имя домена не должно включать специальных символов"),

                default
                => null,
            },
            hint: __("Имя домена должно быть без спец. символов"),
            default: 'Domain'
        );

        $this->domain = str($domain)->lower()->ucfirst();

        return $this;
    }
}
