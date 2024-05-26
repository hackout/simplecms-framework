<?php

use Intervention\Image\ImageManager;


/**
 * 创建验证码
 * 
 * @param string $config
 * @return array|ImageManager|mixed
 * @throws Exception
 */
function captcha(string $config = 'default')
{
    return app('captcha')->create($config);
}