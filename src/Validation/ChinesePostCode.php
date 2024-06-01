<?php
namespace SimpleCMS\Framework\Validation;

/**
 * 中国邮政编码
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 */
class ChinesePostCode
{
    protected string $chinesePostCode;

    protected $regex = '^[1-9]\d{5}(?!\d)$';

    /**
     * IDCard constructor.
     * @param string $chinesePostCode
     */
    public function __construct(string $chinesePostCode)
    {
        $this->setChinesePostCode($chinesePostCode);
    }

    /**
     * Get ChinesePostCode
     */
    public function getChinesePostCode()
    {
        return (string) $this->chinesePostCode;
    }

    /**
     * @param mixed $chinesePostCode
     */
    public function setChinesePostCode(string $chinesePostCode)
    {
        $this->chinesePostCode = (string) trim(strtoupper($chinesePostCode));
    }

    /**
     * 邮政编码是否有效
     * @return bool
     */
    public function isValid(): bool
    {
        if (strlen($this->chinesePostCode) != 18) {
            return false;
        }
        return preg_match($this->regex, $this->chinesePostCode);
    }

}