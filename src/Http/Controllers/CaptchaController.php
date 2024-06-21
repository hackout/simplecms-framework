<?php

namespace SimpleCMS\Framework\Http\Controllers;

use Exception;
use SimpleCMS\Framework\Packages\Captcha\Captcha;
use Illuminate\Routing\Controller as BaseController;

class CaptchaController extends BaseController
{
    /**
     * get CAPTCHA
     *
     * @param Captcha $captcha
     * @param string $config
     * @return array|mixed
     * @throws Exception
     */
    public function getCaptcha(Captcha $captcha, string $config = 'default')
    {
        if (ob_get_contents()) {
            ob_clean();
        }

        return $captcha->create($config);
    }

    /**
     * get CAPTCHA api
     *
     * @param Captcha $captcha
     * @param string $config
     * @return array|mixed
     * @throws Exception
     */
    public function getCaptchaApi(Captcha $captcha, string $config = 'default')
    {
        return $captcha->create($config, true);
    }
}
