<?php

namespace App\Console\Commands\Core\Actions;

final class ApplicationRegistration extends ComposerLayerIntegration
{
    public function run(): void
    {
        $this->registerApp()
            ->registerDomain()
            ->composerReplace()
            ->createScope();
    }

    private function createScope(): self
    {
        if (!mkdir("{$this->basePath}/{$this->application}")) {
            throw new \Exception(__("Не удалось создать директорию приложения"));
        }

        if (!mkdir("{$this->basePath}/{$this->application}/{$this->domain}")) {
            throw new \Exception(__("Не удалось создать домен"));
        }

        return $this;
    }
}
