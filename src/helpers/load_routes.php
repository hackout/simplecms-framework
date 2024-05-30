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
    $allRoutes = scandir(base_path($path));
    foreach ($allRoutes as $routeFile) {
        if (strstr($routeFile, '.php')) {
            Route::group(['prefix' => str_replace('.php', '', $routeFile)], base_path($path . $routeFile));
        }
    }
}