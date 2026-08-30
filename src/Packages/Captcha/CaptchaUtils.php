<?php
namespace SimpleCMS\Framework\Packages\Captcha;


/**
 * 验证码工具类
 */
class CaptchaUtils
{
    /**
     * Returns the md5 short version of the key for cache
     *
     * @param string $key
     * @return string
     */
    public function get_cache_key($key)
    {
        return 'captcha_' . md5($key);
    }
}