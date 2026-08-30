<?php

/**
 * 检查字符串是否为有效的 URL
 *
 * @param mixed $str
 * @return bool
 */
function is_url($str): bool
{
    if (! is_string($str) || trim($str) === '') {
        return false;
    }

    return filter_var($str, FILTER_VALIDATE_URL) !== false;
}