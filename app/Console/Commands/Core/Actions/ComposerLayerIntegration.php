<?php

namespace App\Console\Commands\Core\Actions;

use function Laravel\Prompts\select;
use function Laravel\Prompts\warning;

class ComposerLayerIntegration extends DomainInitAction
{
    /**
     * composer.json
     */
    readonly private string $composerjson;

    public function __construct()
    {
        parent::__construct();
        $this->composerjson = 'composer.json';
        $this->composerJsonPath = "{$this->basePath}/{$this->composerjson}";
    }

    public function composerReplace(): self
    {
        $yes = __('Да');
        $no = __('Нет');

        $accessReplaceComposerFile = select(
            label: "Я внесу новый неймспейс в composer.json ?",
            options: [
                &$yes,
                &$no,
            ],
            hint: "Предупреждение о рейплейсе данных",
        );

        if ($accessReplaceComposerFile === $yes) {
            file_put_contents($this->composerJsonPath, $this->getChangedComposerjson());
        } else {
            warning(__("Разрешение не предоставлено"));
        }

        return $this;
    }

    private function getChangedComposerjson(): array
    {
        $result = [];

        /** @var string $quotes кавычки */
        $quotes = '"';

        /** @var string $colon двоеточие */
        $colon = ': ';

        /** @var string $comma запятая */
        $comma = ',' . PHP_EOL;

        $composerAutloadSection = "{$quotes}autoload{$quotes}";
        $namespace = $quotes . str($this->application)->ucfirst() . '\\\\' . $quotes;
        $namespacePath = "{$quotes}{$this->application}/{$this->domain}/{$quotes}{$comma}";

        /** @var string $autoload сопоставление пути в секции автозагрузки */
        $autoload = "{$namespace}{$colon}{$namespacePath}";

        $composerFile = collect(file($this->composerJsonPath));

        $appPathInit = "{$this->basePath}/{$this->application}";

        $composerFile->map(function ($row, $key) use ($composerFile, $quotes, $autoload, $composerAutloadSection, &$result) {
            if (str($row)->contains($composerAutloadSection)) {
                $appLaravelNamespace = array_slice($composerFile->toArray(), $key + 2, 1);
                $tabs = str($appLaravelNamespace[0])->before($quotes);

                $count = $composerFile->count();
                $chunk1 = array_slice($composerFile->toArray(), 0, $key + 2);
                $chunk2 = array_slice($composerFile->toArray(), $key + 2);

                array_push($chunk1, "{$tabs}{$autoload}");

                $result = array_merge($chunk1, $chunk2);
            }
        });

        return $result;
    }
}
