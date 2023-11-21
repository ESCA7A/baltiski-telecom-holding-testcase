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
 * @property string $excludeDirectoryMatch
 * @method Collection|string getApplicationBasePath()
 */
final class ApplicationIterator
{
    /**
     * Исключаемый паттерн текущей директории и внешней
     */
    readonly protected string $excludeDirectoryMatch;

    public function __construct(string $anyDonain = 'domain', ?string $rootApplicationDir = null)
    {
        $this->excludeDirectoryMatch = '/[.]+/';
        $this->domainName = $anyDonain;
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
            $composerRootName = $this->getRootApplicationDirFromComposer();
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
        try {
            $basePath = base_path($this->rootApplicationDir);
            $rdi = new RecursiveDirectoryIterator($basePath, FilesystemIterator::KEY_AS_PATHNAME);
            $recursiveIterator = new RecursiveIteratorIterator($rdi);
            $pathsCollect = collect();
        } catch (Throwable $e) {

        }


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
            $domainPath = collect($composerjson)->filter(fn ($row) => Str::contains($row, $this->domainName, $ignoreCase = true));
            $rootDirName = str($domainPath->first())->after(':')->before('/')->replace(['"', ' '], '');
        } catch (Throwable $e) {
            error_log(__('Не удалось определить директорию приложения. :msg', ['msg' => $e->getMessage()]));
        }

        return $rootDirName;
    }

    /**
     * Возвращает все имена доменов из директории приложения
     */
    public function getDomainNames(): Collection
    {
        try {
            $appPath = $this->getApplicationBasePath()->implode('/');
            $domains = collect(scandir($appPath))->filter(fn ($item) => !Str::isMatch('/[.]+/', $item));

            return $domains;
        } catch (Throwable $e) {
            error_log(__('Поиск доменных имен завершился ошибкой: :msg', ['msg' => $e->getMessage()]));
        }
    }

    /**
     * Возвращает все пути доменов из директории приложения
     */
    public function getDomainPaths(): Collection
    {
        try {
            $basePath = collect([$this->getApplicationBasePath()->implode('/')]);
            $domainNames = $this->getDomainNames();
            $crossJoin = Arr::crossJoin($basePath->toArray(), $domainNames->toArray());

            return collect($crossJoin)->map(fn ($path) => implode('/', $path));
        } catch (Throwable $e) {
            error_log(__('Во время поиска доменных путей возникла ошибка: :msg', ['msg' => $e->getMessage()]));
        }
    }

    /**
     * Возвращает путь домена по названию
     */
    public function getPathByDomain(string $domain): Collection
    {
        try {
            $domain = str($domain)->lower()->ucfirst();
            $domainPaths = $this->getDomainPaths();

            return $domainPaths->filter(fn ($path) => Str::contains($path, $domain));

        } catch (Throwable $e) {
            error_log(__('Поиск домена по переданному имени завершился ошибкой: :msg', ['msg' => $e->getMessage()]));
        }
    }

    /**
     * Возвращает список модулей (Коллекция коллекций) в проекте по домену (если передан) либо всех доменов
     */
    public function getModulesList(?string $domain = null): Collection
    {
        try {
            if ($domain) {
                $domain = str($domain)->lower()->ucfirst();
                $domainPath = $this->getPathByDomain($domain);
                $path = $domainPath->implode('');
                $dirs = collect(scandir($path));
                $modules = $dirs->filter(fn ($dir) => !Str::isMatch($this->excludeDirectoryMatch, $dir));

                return collect([$domain => $modules]);
            }

            $domains = $this->getDomainPaths();
            $modules = $domains->map(function ($domain) {
                $domainName = $this->getDomainByPath($domain);
                $dirs = collect(scandir($domain));
                $modules = $dirs->filter(fn ($dir) => !Str::isMatch($this->excludeDirectoryMatch, $dir));

                return collect([$domainName => $modules]);
            });

            return $modules;
        } catch (Throwable $e) {
            error_log(__('Не удалось получить список модулей: :msg', ['msg' => $e->getMessage()]));
        }
    }

    /**
     * Возвращает имя домена
     */
    public function getDomainByPath(string $path): string
    {
        try {
            return str($path)->between("{$this->rootApplicationDir}/", '/')->before('/');
        } catch (Throwable $e) {
            error_log(__('Домен в пути не найден: :msg', ['msg' => $e->getMessage()]));
        }
    }
}
