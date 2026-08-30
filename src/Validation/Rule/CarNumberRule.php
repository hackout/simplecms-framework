<?php
namespace SimpleCMS\Framework\Validation\Rule;

use Illuminate\Contracts\Validation\ValidationRule;
use SimpleCMS\Framework\Validation\CarNumber;

/**
 * 车牌号
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 */
class CarNumberRule implements ValidationRule
{

    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        if (!$this->passes($value)) {
            $fail($this->message($attribute));
        }
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  mixed  $value
     * @return bool
     */
    public function passes($value)
    {
        return (new CarNumber($value))->isValid();
    }


    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(string $attribute)
    {
        return "The {$attribute} is incorrect.";
    }
}