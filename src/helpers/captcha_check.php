<?php

/**
 * 验证码校验
 * 
 * @param string $value
 * @return bool
 */
function captcha_check(string $value): bool
{
    return app('captcha')->check($value);
}