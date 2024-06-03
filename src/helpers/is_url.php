<?php

/**
 * 检查字符串是否为有效的URL
 * Check if a string is a valid URL
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 * 
 * @param string $str
 * @return bool
 */
function is_url($str): bool
{
    return filter_var($str, FILTER_VALIDATE_URL) !== false;

}