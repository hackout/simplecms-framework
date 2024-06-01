<?php
namespace SimpleCMS\Framework\Validation;

/**
 * 中文
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 */
class Chinese
{
    protected string $chinese;

    protected $regex = '^[\u4e00-\u9fa5]{0,}$';

    /**
     * IDCard constructor.
     * @param string $chinese
     */
    public function __construct(string $chinese)
    {
        $this->setChinese($chinese);
    }

    /**
     * Get Chinese
     */
    public function getChinese()
    {
        return (string) $this->chinese;
    }

    /**
     * @param mixed $chinese
     */
    public function setChinese(string $chinese)
    {
        $this->chinese = (string) trim(strtoupper($chinese));
    }

    /**
     * 中文是否有效
     * @return bool
     */
    public function isValid(): bool
    {
        if (strlen($this->chinese) != 18) {
            return false;
        }
        return preg_match($this->regex, $this->chinese);
    }

}