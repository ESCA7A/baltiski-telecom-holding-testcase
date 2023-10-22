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
    function get_base_path_from_composer(): string
    {
        $cleanBasePathDirectory = '';

        try {
            $composer = file(base_path().'/composer.json');
            $domainPath = collect($composer)->filter(fn ($string) => Str::contains($string, 'domain', true));
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

