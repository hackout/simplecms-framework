<?php
namespace SimpleCMS\Framework\Validation;

/**
 * 校验中文
 */
class Chinese
{
    protected string $chinese;
    protected string $regex = '/^[\x{4e00}-\x{9fa5}]+$/u';

    public function __construct(string $chinese)
    {
        $this->setChinese($chinese);
    }

    public function getChinese()
    {
        return (string) $this->chinese;
    }

    public function setChinese(string $chinese)
    {
        $this->chinese = (string) trim($chinese);
    }

    public function isValid(): bool
    {
        return (bool) preg_match($this->regex, $this->chinese) === 1;
    }
}