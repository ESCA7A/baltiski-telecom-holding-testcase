<?php

use Illuminate\Support\Str;
use Illuminate\Support\Collection;

if (!function_exists('get_files_path_by_prefix')) {
    function get_files_path_by_prefix(string $prefix, string $basePath = 'application'): Collection
    {
        $basePath = base_path($basePath);
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

if (!function_exists('get_dirs_path_by_prefix')) {
    function get_dirs_path_by_prefix(string $prefix, string $basePath = 'application'): Collection
    {
        $basePath = base_path($basePath);
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
