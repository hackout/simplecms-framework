<?php

/**
 * 验证码地址
 * 
 * @param string $config
 * @return string
 */
function captcha_src(string $config = 'default'): string
{
    return app('captcha')->src($config);
}