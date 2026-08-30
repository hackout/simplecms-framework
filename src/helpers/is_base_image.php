<?php

/**
 * 检查字符串是否为 Base64 编码的图片
 *
 * @param mixed $str
 * @return bool
 */
function is_base_image($str): bool
{
    if (! is_string($str) || $str === '') {
        return false;
    }

    $decoded = base64_decode($str, true);
    if ($decoded === false) {
        return false;
    }

    if (! function_exists('finfo_open') || ! function_exists('finfo_buffer') || ! function_exists('finfo_close')) {
        return preg_match('/^data:image\/(?:png|jpe?g|gif|webp|bmp|svg\+xml);base64,/', $str) === 1;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_buffer($finfo, $decoded);
    finfo_close($finfo);

    return is_string($mimeType) && str_starts_with($mimeType, 'image/');
}