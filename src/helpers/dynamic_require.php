<?php

use Illuminate\Support\Facades\Route;

/**
 * 动态加载文件
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 * @param string $path
 * @return void
 */
function dynamic_require(string $path): void
{
    $path = base_path($path);
    if (is_dir($path)) {
        foreach (scandir($path) as $file) {
            $filePath = $path . DIRECTORY_SEPARATOR . $file;

            if (!is_file($path)) {
                continue;
            }

            if (strstr($filePath, '.php')) {
                require_once $filePath;
            }
        }
    } else {
        logger("The path $path does not exists.");
    }
}