<?php

/**
 * 检查字符串是否为UTF-8编码
 * Check if a string is UTF-8 encoded
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 * 
 * @param string $str
 * @return bool
 */
function is_utf8($str): bool
{
    return mb_check_encoding($str, 'UTF-8');
}