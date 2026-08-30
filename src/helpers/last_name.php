<?php

use Illuminate\Support\Str;

/**
 * 获取Class最终名称
 *
 * @param string<class-string> $model
 * @return string
 */
function last_name(string $model): string
{
    $normalized = str_replace('/', '\\', $model);
    $segments = explode('\\', $normalized);

    return Str::afterLast(end($segments), '\\') ?: last($segments);
}