<?php
namespace SimpleCMS\Framework\Packages\AliAPI;

use Darabonba\OpenApi\Models\Config;
use AlibabaCloud\Tea\Exception\TeaError;
use AlibabaCloud\Tea\Utils\Utils\RuntimeOptions;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Dysmsapi;
use SimpleCMS\Framework\Exceptions\SimpleException;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest;

class AliApi
{
    private $runtime;

    private $service;

    public function __construct()
    {
        $config = new Config([
            'accessKeyId' => config('cms.sms.alibaba.access_key_id'),
            'accessKeySecret' => config('cms.sms.alibaba.access_key_secret'),
        ]);
        $config->endpoint = "dysmsapi.aliyuncs.com";
        $this->runtime = new RuntimeOptions();
        $this->runtime->maxIdleConns = 3;
        $this->runtime->connectTimeout = 10000;
        $this->runtime->readTimeout = 10000;
        $this->service = new Dysmsapi($config);
    }

    /**
     * 发送短信
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  string $mobile 手机号码
     * @param  string $sign 签名
     * @param  string $template 模板编号
     * @param  array  $param 参数
     * @return void
     */
    public function sent(string $mobile, string $sign, string $template, array $param)
    {
        $data = [
            'phoneNumbers' => $mobile,
            'signName' => $sign,
            'templateCode' => $template,
            'templateParam' => json_encode($param)
        ];
        $sendSmsRequest = new SendSmsRequest($data);
        try {
            $this->service->sendSmsWithOptions($sendSmsRequest, $this->runtime);
        } catch (\Exception $error) {
            if (!($error instanceof TeaError)) {
                $error = new TeaError([], $error->getMessage(), $error->getCode(), $error);
            }

            throw new SimpleException($error->message);
        }
    }

    /**
     * 发送短信验证码
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  string $mobile 手机号码
     * @param  string $code 验证码
     * @return void
     */
    public function sentCode(string $mobile, string $code)
    {
        $this->sent($mobile, config('cms.sms.alibaba.sign'), config('cms.sms.alibaba.template'), [config('cms.sms.alibaba.template_code') => $code]);
    }
}