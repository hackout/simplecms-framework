<?php
namespace SimpleCMS\Framework\Validation;

/**
 * 车牌号
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 */
class CarNumber
{
    protected string $carNumber;

    protected $regex = '^[京津沪渝冀豫云辽黑湘皖鲁新苏浙赣鄂桂甘晋蒙陕吉闽贵粤青藏川宁琼使领A-Z]{1}[A-Z]{1}[A-Z0-9]{4}[A-Z0-9挂学警港澳]{1}$';

    /**
     * IDCard constructor.
     * @param string $carNumber
     */
    public function __construct(string $carNumber)
    {
        $this->setCarNumber($carNumber);
    }

    /**
     * Get CarNumber
     */
    public function getCarNumber()
    {
        return (string) $this->carNumber;
    }

    /**
     * @param mixed $carNumber
     */
    public function setCarNumber(string $carNumber)
    {
        $this->carNumber = (string) trim(strtoupper($carNumber));
    }

    /**
     * 车牌号是否有效
     * @return bool
     */
    public function isValid(): bool
    {
        return preg_match($this->regex, $this->carNumber);
    }

}