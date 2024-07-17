<?php
namespace SimpleCMS\Framework\Packages\Captcha;

use function is_string;
use function str_split;
use Illuminate\Support\Facades\Crypt;

/**
 * 验证码文本处理类
 * 
 * @use Captcha
 * @abstract Captcha
 */
trait CaptchaText
{
    /**
     * Generate captcha text
     *
     * @return array
     */
    protected function generate(): array
    {
        $characters = (array) (is_string($this->characters) ? str_split($this->characters) : $this->characters);
        $bag = [];
        if ($this->math) {
            $x = random_int(10, 30);
            $y = random_int(1, 9);
            $bag = "$x + $y = ";
            $key = $x + $y;
            $key .= '';
        } else {
            if (!empty($characters)) {
                for ($i = 0; $i < $this->length; $i++) {
                    $char = $characters[rand(0, count($characters) - 1)];
                    $bag[] = $this->sensitive ? $char : $this->str->lower($char);
                }
            }
            $key = implode('', $bag);
        }
        $hash = $this->hasher->make($key);
        if ($this->encrypt)
            $hash = Crypt::encrypt($hash);
        $this->session->put('captcha', [
            'sensitive' => $this->sensitive,
            'key' => $hash,
            'encrypt' => $this->encrypt
        ]);
        return [
            'value' => $bag,
            'sensitive' => $this->sensitive,
            'key' => $hash
        ];
    }

    
    /**
     * Random font size
     *
     * @return int
     */
    protected function fontSize(): int
    {
        return rand($this->image->height() - 10, $this->image->height());
    }

    /**
     * Random font color
     *
     * @return string
     */
    protected function fontColor(): string
    {
        if (!empty($this->fontColors)) {
            $color = $this->fontColors[rand(0, count($this->fontColors) - 1)];
        } else {
            $color = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        }

        return $color;
    }
}