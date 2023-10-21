<?php

use Illuminate\Support\Str;
use Illuminate\Support\Collection;

if (!function_exists('get_files_path_by_prefix')) {
    function get_files_path_by_prefix(string $prefix): Collection
    {
        $basePath = base_path('application');
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
            $pathContainRoutesDir = Str::contains($pathName, $prefix);

            if ($pathContainRoutesDir) {
                $pathsCollect->push($pathName);
            }
        }

        return $pathsCollect;
    }
}
