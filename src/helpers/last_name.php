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

    $model = str_replace('/', '\\', $model);
    return Str::afterLast(basename($model), "\\");
}