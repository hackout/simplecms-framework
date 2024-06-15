<?php
namespace SimpleCMS\Framework\Validation\Rule;

use Illuminate\Contracts\Validation\Rule;
use SimpleCMS\Framework\Validation\Mobile;

/**
 * 手机号码验证
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 */
class MobileRule implements Rule
{
    public function validate($attribute, $value, $parameters)
    {
        return $this->passes($attribute,$value);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return (new Mobile($value))->isValid();
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        return 'The Mobile Number is incorrect.';
    }
}