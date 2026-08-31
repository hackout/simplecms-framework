<?php

use Illuminate\Support\Facades\Route;

/**
 * Load Backend Routes
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 * @param string $path
 * @return void
 */
function load_routes(string $path): void
{
    $fullPath = $path;

    if (! str_starts_with($path, DIRECTORY_SEPARATOR) && ! preg_match('#^[A-Za-z]:[\\\\/]#', $path)) {
        $fullPath = base_path($path);
    }

    if (! is_dir($fullPath)) {
        logger("The path {$fullPath} does not exist.");
        return;
    }

    $allRoutes = scandir($fullPath);
    if ($allRoutes === false) {
        logger("Unable to read path {$fullPath}.");
        return;
    }

    foreach ($allRoutes as $routeFile) {
        if ($routeFile === '.' || $routeFile === '..') {
            continue;
        }

        $routeFilePath = $fullPath . DIRECTORY_SEPARATOR . $routeFile;

        if (! is_file($routeFilePath) || ! str_ends_with($routeFile, '.php')) {
            continue;
        }

        $prefix = pathinfo($routeFile, PATHINFO_FILENAME);
        if ($prefix === '' || $prefix === '.' || $prefix === '..') {
            continue;
        }

        Route::prefix($prefix)->group(function () use ($routeFilePath) {
            require $routeFilePath;
        });
    }
}