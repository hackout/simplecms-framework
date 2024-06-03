<?php

/**
 * 检查字符串是否为Base64编码
 * Check if a string is Base64 encoded
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 * 
 * @param string $str
 * @return bool
 */
function is_base64($str): bool
{
    return base64_encode(base64_decode($str, true)) === $str;
}