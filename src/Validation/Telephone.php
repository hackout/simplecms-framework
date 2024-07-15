<?php
namespace SimpleCMS\Framework\Validation;

/**
 * 座机号码验证类
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 */
class Telephone
{
    protected $telephone;

    protected $regex = '/^0\d{2,3}\d{7,8}$/';

    /**
     * Telephone constructor.
     * @param $telephone
     */
    public function __construct($telephone)
    {
        $this->setTelephone($telephone);
    }

    /**
     * @param mixed $id
     */
    public function setTelephone($telephone)
    {
        $this->telephone = trim($telephone);
    }

    /**
     * 电话号码是否有效
     * @return bool
     */
    public function isValid(): bool
    {
        if (!in_array(strlen($this->telephone), [10, 11, 12])) {
            return false;
        }
        return (bool) preg_match($this->regex, $this->telephone);
    }

}