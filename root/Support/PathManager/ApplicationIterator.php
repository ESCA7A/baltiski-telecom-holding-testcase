<?php

namespace Support\PathManager;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Support\PathManager\Contracts\FileIterableContract;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * @method Collection|string getApplicationBasePath()
 */
final class ApplicationIterator
{
    public function __construct(string $anyDonain = 'domain', ?string $rootApplicationDir = null)
    {
        $this->baseDomain = $anyDonain;
        $this->rootApplicationDir = $rootApplicationDir ?? $this->getRootApplicationDirFromComposer();
    }

    /**
     * Метод возвращает абсолютный путь до доменной области приложения
     *
     * @var bool $resultAsString
     * @var string $rootName
     */
    public function getApplicationBasePath(bool $resultAsString = false, string $rootName = 'root'): Collection|string
    {

        try {
            $composerRootName = get_base_path_from_composer();
            $basePath = base_path();
            $rootName =& $composerRootName ?? $rootName;
        } catch (Throwable $e) {
            error_log($e->getMessage());
        }

        try {
            if ($resultAsString) {
                return "{$basePath}/{$rootName}";
            }
        } catch (Throwable $e) {
            error_log($e->getMessage());
        }

        try {
            return collect([$basePath, $rootName]);
        } catch (Throwable $e) {
            error_log($e->getMessage());
        }
    }

    /**
     * Метод рекурсивно итерирует директории и возвращает абсолютный путь к файлам имеющим в пути заданный префикс
     */
    public function getFilesPathByPrefix(string $prefix, ?string $basePath = null): Collection
    {
        $basePath = base_path($this->rootApplicationDir);
        $rdi = new RecursiveDirectoryIterator($basePath, FilesystemIterator::KEY_AS_PATHNAME);
        $recursiveIterator = new RecursiveIteratorIterator($rdi);
        $pathsCollect = collect();

        /**
         * @var RecursiveDirectoryIterator $item
         */
        foreach ($recursiveIterator as $item) {
            if ($item->isDir()) {
                continue;
            }

            $pathName = $item->getPathname();
            $pathContainDir = Str::contains($pathName, $prefix);

            if ($pathContainDir) {
                $pathsCollect->push($pathName);
            }
        }

        return $pathsCollect;
    }

    /**
     * Метод рекурсивно итерирует директории и возвращает абсолютный путь к директориям имеющим в пути заданный префикс
     */
    public function getDirsPathByPrefix(string $prefix, ?string $basePath = null): Collection
    {
        $basePath = base_path($this->rootApplicationDir);
        $rdi = new RecursiveDirectoryIterator($basePath, FilesystemIterator::KEY_AS_PATHNAME);
        $recursiveIterator = new RecursiveIteratorIterator($rdi);
        $pathsCollect = collect();

        /**
         * @var RecursiveDirectoryIterator $item
         */
        foreach ($recursiveIterator as $item) {
            if ($item->isFile()) {
                continue;
            }

            $pathName = $item->getPathname();
            $pathContainDir = Str::contains($pathName, $prefix);

            if ($pathContainDir) {
                $pathName = str($pathName)->replace(['.', '..'], '');

                if ($pathsCollect->contains($pathName)) {
                    continue;
                }

                $pathsCollect->push($pathName);
            }
        }

        return $pathsCollect;
    }

    /**
     * Метод возвращает имя_директории в которой регистрируются все домены в приложении
     */
    public function getRootApplicationDirFromComposer(): string
    {
        $cleanBasePathDirectory = '';
        $composerjson = 'composer.json';
        $basePath = base_path();

        try {
            $composerjson = file("{$basePath}/{$composerjson}");
            $domainPath = collect($composerjson)->filter(fn ($row) => Str::contains($row, $this->baseDomain, $ignoreCase = true));
            $rootDirName = str($domainPath->first())->after(':')->before('/')->replace(['"', ' '], '');
        } catch (Throwable $e) {
            error_log(__('Не удалось определить директорию приложения. :message', [
                'message' => $e->getMessage(),
            ]));
        }

        return $rootDirName;
    }

    /**
     * Возвращает все имена доменов даже если они не зарегистрированы в автозагрузчике
     */
    public function get_domain_names(): Collection
    {
        try {
            $appPath = get_application_path()->implode('/');
            $dirs = collect(scandir($appPath));

            return $dirs->filter(fn ($domain) => !Str::isMatch('/[.]+/', $domain));
        } catch (Throwable $e) {
            error_log(__("Что-то пошло не так: :msg", ['msg' => $e->getMessage()]));
        }
    }

    /**
     * Возвращает все пути доменов даже если они не зарегистрированы в автозагрузчике
     */
    public function get_domain_paths(): Collection
    {
        try {
            $basePath = collect([get_application_path()->implode('/')]);
            $domainNames = get_domain_names();
            $crossJoin = Arr::crossJoin($basePath->toArray(), $domainNames->toArray());

            return collect($crossJoin)->map(fn ($path) => implode('/', $path));
        } catch (Throwable $e) {
            error_log(__("Что-то пошло не так: :msg", ['msg' => $e->getMessage()]));
        }
    }

    /**
     * Возвращает путь домена по его названию
     *
     * (даже если домен не зарегистрирован в секции "autoload")
     */
    public function get_path_by_domain(string $domain): Collection
    {
        try {
            $domainPaths = get_domain_paths();

            return $domainPaths->filter(fn ($path) => Str::contains($path, $domain));

        } catch (Throwable $e) {
            error_log(__("Что-то пошло не так: :msg", ['msg' => $e->getMessage()]));
        }
    }

    /**
     * Возвращает список модулей (Коллекция коллекций) в проекте по домену (если передан) либо всех доменов
     *
     * (даже если домены не зарегистрированы в секции "autoload")
     */
    public function get_modules_list(?string $domain = null): Collection
    {
        try {
            if ($domain) {
                $domainPath = get_path_by_domain($domain);
                $path = $domainPath->implode('');
                $dirs = collect(scandir($path));
                $modules = $dirs->filter(fn ($dir) => !Str::isMatch('/[.]+/', $dir));

                return collect([$domain => $modules]);
            }

            $domains = get_domain_paths();
            $modules = $domains->map(function ($domain) {
                $domainName = get_domain_by_path($domain);
                $dirs = collect(scandir($domain));
                $modules = $dirs->filter(fn ($dir) => !Str::isMatch('/[.]+/', $dir));

                return collect([$domainName => $modules]);
            });

            return $modules;
        } catch (Throwable $e) {
            error_log(__("Что-то пошло не так: :msg", ['msg' => $e->getMessage()]));
        }
    }

    /**
     * Возвращает имя домена
     *
     * (даже если домен не зарегистрирован в секции "autoload")
     */
    public function get_domain_by_path(string $path): string
    {
        try {
            return Str::afterLast($path, '/');
        } catch (Throwable $e) {
            error_log(__("Что-то пошло не так: :msg", ['msg' => $e->getMessage()]));
        }
    }
}
