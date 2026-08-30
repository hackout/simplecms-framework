<?php

use Illuminate\Support\Facades\File;
use SimpleCMS\Framework\Exceptions\SimpleException;

/**
 * Convert an image file to a base64 data URL.
 *
 * @param string $path
 * @throws \SimpleCMS\Framework\Exceptions\SimpleException
 * @return string
 */
function image_to_base64(string $path): string
{
    if (! File::exists($path) || ! File::isFile($path)) {
        throw new SimpleException('The file does not exist.');
    }

    $type = File::mimeType($path);
    $file = File::get($path);

    if (! is_string($type) || ! is_string($file)) {
        throw new SimpleException('The file could not be read.');
    }

    return 'data:' . $type . ';base64,' . base64_encode($file);
}