<?php

use Illuminate\Support\Facades\File;
use SimpleCMS\Framework\Exceptions\SimpleException;

/**
 * 图片转base64
 * 
 * @param string $path
 * @throws \SimpleCMS\Framework\Exceptions\SimpleException
 * @return string
 */
function image_to_base64(string $path): string
{
    $type = File::mimeType($path);
    $file = File::get($path);
    if (!is_string($type)) {
        throw new SimpleException('The file is not exists.');
    }
    $base64 = 'data:' . $type . ';base64,' . base64_encode($file);
    return $base64;
}