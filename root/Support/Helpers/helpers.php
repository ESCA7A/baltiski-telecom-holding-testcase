<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

if (! function_exists('get_application_path')) {
    /**
     * Функция возвращает путь до рутовой директории приложения
     *
     * - Рутовая директория: директория в которой находятся слои
     */
    function get_application_path(bool $implodeResult = false): Collection
    {
        try {
            if ($implodeResult) {
                collect([base_path(), get_base_path_from_composer()])->implode('/');
            }

            return collect([base_path(), get_base_path_from_composer()]);
        } catch (Throwable $e) {
            error_log(__("Что-то пошло не так: :msg", ['msg' => $e->getMessage()]));
        }
    }
}

if (! function_exists('get_files_path_by_prefix')) {
    /**
     * Функция возвращает абсолютный путь к файлам имеющим в пути префикс
     *
     * Из-за того что функция работает рекурсивно, префикс обязан быть наиболее ближайшим к искомым файлам
     */
    function get_files_path_by_prefix(string $prefix, ?string $basePath = null): Collection
    {
        $baseNamespace = is_null($basePath) ? get_base_path_from_composer() : $basePath;
        $basePath = base_path($baseNamespace);
        $recursiveIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($basePath, FilesystemIterator::KEY_AS_PATHNAME));
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
}

if (! function_exists('get_dirs_path_by_prefix')) {
    /**
     * Функция возвращает абсолютный путь к директориям имеющим в пути префикс
     *
     * Из-за того что функция работает рекурсивно, префикс обязан быть наиболее ближайшим к искомым директориям
     */
    function get_dirs_path_by_prefix(string $prefix, ?string $basePath = null): Collection
    {
        $baseNamespace = is_null($basePath) ? get_base_path_from_composer() : $basePath;
        $basePath = base_path($baseNamespace);
        $recursiveIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($basePath, FilesystemIterator::KEY_AS_PATHNAME));
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
                $pathsCollect->push($pathName);
            }
        }

        return $pathsCollect;
    }
}

if (! function_exists('get_base_path_from_composer')) {
    /**
     * Функция возвращает @имя_директории в которой регистрируются все слои в приложении
     * Для того что бы функция работала корректно, вам необходимо передать имя зарегистрированного слоя
     * в composer.json секция "autoload": "Namespace\\" : "@имя_директории/Название_Домена"
     *
     * Имя директории - это название первого вложения в строке объявления autoload секции в composer.json
     *
     */
    function get_base_path_from_composer(string $layer = 'domain'): string
    {
        $cleanBasePathDirectory = '';

        try {
            $composer = file(base_path().'/composer.json');
            $domainPath = collect($composer)->filter(fn ($string) => Str::contains($string, $layer, true));
            $namespace = Str::between($domainPath->first(), ':', '/');
            $dirtyBasePathDirectory = Str::before($namespace, '/');
            $cleanBasePathDirectory = Str::replace(['', ' ', '"'], '', $dirtyBasePathDirectory);
        } catch (Throwable $e) {
            error_log(__('Не удалось определить путь для каркаса. :message', [
                'message' => $e->getMessage(),
            ]));
        }

        return $cleanBasePathDirectory;
    }
}

if (! function_exists('get_domain_names')) {
    /**
     * Возвращает все имена доменов даже если они не зарегистрированы в автозагрузчике
     */
    function get_domain_names(): Collection
    {
        try {
            $appPath = get_application_path()->implode('/');
            $dirs = collect(scandir($appPath));

            return $dirs->filter(fn ($domain) => !Str::isMatch('/[.]+/', $domain));
        } catch (Throwable $e) {
            error_log(__("Что-то пошло не так: :msg", ['msg' => $e->getMessage()]));
        }
    }
}

if (! function_exists('get_domain_paths')) {
    /**
     * Возвращает все пути доменов даже если они не зарегистрированы в автозагрузчике
     */
    function get_domain_paths(): Collection
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
}

if (! function_exists('get_path_by_domain')) {
    /**
     * Возвращает путь домена по его названию
     *
     * (даже если домен не зарегистрирован в секции "autoload")
     */
    function get_path_by_domain(string $domain): Collection
    {
        try {
            $domainPaths = get_domain_paths();

            return $domainPaths->filter(fn ($path) => Str::contains($path, $domain));

        } catch (Throwable $e) {
            error_log(__("Что-то пошло не так: :msg", ['msg' => $e->getMessage()]));
        }
    }
}

if (! function_exists('get_modules_list')) {
    /**
     * Возвращает список модулей в проекте по домену (если передан) либо всех доменов
     *
     * (даже если домены не зарегистрированы в секции "autoload")
     */
    function get_modules_list(?string $domain = null): Collection
    {
        try {
            if ($domain) {
                $domainPath = get_path_by_domain($domain);
                $path = $domainPath->implode('');
                $dirs = collect(scandir($path));
                $modules = $dirs->filter(fn ($dir) => !Str::isMatch('/[.]+/', $dir));

                return collect([$domain => $modules->toArray()]);
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
}

if (! function_exists('get_domain_by_path')) {
    /**
     * Возвращает имя домена
     *
     * (даже если домен не зарегистрирован в секции "autoload")
     */
    function get_domain_by_path(string $path): string
    {
        try {
            return Str::afterLast($path, '/');
        } catch (Throwable $e) {
            error_log(__("Что-то пошло не так: :msg", ['msg' => $e->getMessage()]));
        }
    }
}

if (! function_exists('module_is_exist')) {
    /**
     * Существует ли модуль ?
     *
     * (Работает даже если домен не зарегистрирован в секции "autoload")
     */
    function module_is_exist(string $domainName, string $modulename): bool
    {
        try {
            $domainModulesList = get_modules_list($domainName);

            return $domainModulesList->contains(function ($modules) use ($modulename) {
                $modules = collect($modules);
                return $modules->contains($modulename);
            });
        } catch (Throwable $e) {
            error_log(__("Что-то пошло не так: :msg", ['msg' => $e->getMessage()]));
        }
    }
}

