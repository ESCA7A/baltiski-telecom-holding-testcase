<?php

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

if (! function_exists('get_files_path_by_prefix')) {
    function get_files_path_by_prefix(string $prefix, string $basePath = 'application'): Collection
    {
        $baseNamespace = get_base_path_from_composer() ?? $basePath;
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
    function get_dirs_path_by_prefix(string $prefix, string $basePath = 'application'): Collection
    {
        $baseNamespace = get_base_path_from_composer() ?? $basePath;
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
