<?php

use Illuminate\Support\Str;

/**
 * 星号掩码
 * @author Dennis Lui <hackout@vip.qq.com>
 *
 * @param string $string 字符串
 * @param string $start 前面数字长度
 * @param string $end 结尾数字长度
 * @return string
 */
function safe_code(string $string,int $start = 3,int $end = 3): string
{
    return Str::of($string)->mask('*', $start, ($end * -1));
}