<?php

/**
 * 检查字符串是否为Base64编码的图片
 * Check if a string is a Base64 encoded image
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 * 
 * @param string $str
 * @return bool
 */
function is_base_image($str): bool
{
    $image = base64_decode($str, true);
    $finfo = finfo_open();
    $mime_type = finfo_buffer($finfo, $image, FILEINFO_MIME_TYPE);
    finfo_close($finfo);
    return substr($mime_type, 0, 5) === 'image';
}