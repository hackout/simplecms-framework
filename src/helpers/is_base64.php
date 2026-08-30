<?php

/**
 * 检查字符串是否为 Base64 编码
 *
 * @param mixed $str
 * @return bool
 */
function is_base64($str): bool
{
    if (! is_string($str) || $str === '') {
        return false;
    }

    $decoded = base64_decode($str, true);

    if ($decoded === false) {
        return false;
    }

    return base64_encode($decoded) === $str;
}