<?php
namespace SimpleCMS\Framework\Validation\Rule;

use Illuminate\Contracts\Validation\Rule;
use SimpleCMS\Framework\Validation\Mobile;
use SimpleCMS\Framework\Validation\Telephone;

/**
 * 电话号码验证
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 */
class PhoneRule implements Rule
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
        return (new Telephone($value))->isValid() || (new Mobile($value))->isValid();
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        return 'The Phone Number is incorrect.';
    }
}