<?php


/**
 * 验证码API校验
 * 
 * @param string $value
 * @param string $key
 * @param string $config
 * @return bool
 */
function captcha_api_check(string $value, string $key, string $config = 'default'): bool
{
    return app('captcha')->check_api($value, $key, $config);
}