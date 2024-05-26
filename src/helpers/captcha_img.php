<?php

/**
 * 验证码图片
 * 
 * @param string $config
 * @param array $attrs
 * @return string
 */
function captcha_img(string $config = 'default', array $attrs = []): string
{
    return app('captcha')->img($config, $attrs);
}