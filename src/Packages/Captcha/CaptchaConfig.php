<?php
namespace SimpleCMS\Framework\Packages\Captcha;


/**
 * 验证码配置类
 * 
 * @use CaptchaAbstract
 * @abstract CaptchaAbstract
 */
trait CaptchaConfig
{

    /**
     * Configure captcha settings
     *
     * @param string $config
     * @return void
     */
    protected function configure($config)
    {
        if ($this->config->has('cms.captcha.' . $config)) {
            foreach ($this->config->get('cms.captcha.' . $config) as $key => $val) {
                $this->{$key} = $val;
            }
        }
    }
}