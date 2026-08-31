<?php

/**
 * 动态加载文件
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 * @param string $path
 * @return void
 */
function dynamic_require(string $path): void
{
    $fullPath = $path;

    if (! str_starts_with($path, DIRECTORY_SEPARATOR) && ! preg_match('#^[A-Za-z]:[\\/]#', $path)) {
        $fullPath = base_path($path);
    }

    if (! is_dir($fullPath)) {
        logger("The path {$fullPath} does not exist.");
        return;
    }

    $files = scandir($fullPath);
    if ($files === false) {
        logger("Unable to read path {$fullPath}.");
        return;
    }

    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        $filePath = $fullPath . DIRECTORY_SEPARATOR . $file;
        if (! is_file($filePath) || ! str_ends_with($filePath, '.php')) {
            continue;
        }

        require_once $filePath;
    }
}