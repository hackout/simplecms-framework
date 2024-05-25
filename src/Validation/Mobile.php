<?php
namespace SimpleCMS\Framework\Validation;

/**
 * 手机号码验证类
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 */
class Mobile
{
    protected $mobile;

    protected $regex = '/^1[3456789]\d{9}$/';

    /**
     * Mobile constructor.
     * @param $mobile
     */
    public function __construct($mobile)
    {
        $this->setMobile($mobile);
    }

    /**
     * @param mixed $id
     */
    public function setMobile($mobile)
    {
        $this->mobile = trim($mobile);
    }

    /**
     * 手机号码是否有效
     * @return bool
     */
    public function isValid(): bool
    {
        if (strlen($this->mobile) != 11) {
            return false;
        }
        return preg_match($this->regex, $this->mobile);
    }

}