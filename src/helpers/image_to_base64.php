<?php

use Illuminate\Support\Facades\File;

/**
 * 图片转base64
 * Check if a string is Base64 encoded
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 * 
 * @param string $path
 * @return string
 */
function image_to_base64(string $path): string
{
    $path = public_path($path);
    $type = File::mimeType($path);
    $file = File::get($path);
    $base64 = 'data:' . $type . ';base64,' . base64_encode($file);
    return $base64;
}