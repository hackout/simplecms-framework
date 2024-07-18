<?php
namespace SimpleCMS\Framework\Packages\Captcha;

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
        $bag = $this->getGenerateBag();
        $hash = $this->hasher->make($this->getGenerateKey($bag));
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

    private function getGenerateCharacters(): array
    {
        if (is_array($this->characters)) {
            return $this->characters;
        }
        return (array) str_split($this->characters);
    }

    private function getGenerateBag(): array
    {
        $bag = [];
        if ($this->math) {
            $x = random_int(10, 30);
            $y = random_int(1, 9);
            $text = "$x + $y = ";
            $bag = (array) str_split($text);
        } else {
            $characters = $this->getGenerateCharacters();
            if (!empty($characters)) {
                for ($i = 0; $i < $this->length; $i++) {
                    $char = $characters[rand(0, count($characters) - 1)];
                    $bag[] = $this->sensitive ? $char : $this->str->lower($char);
                }
            }
        }
        return $bag;
    }

    private function getGenerateKey(array $generate): string
    {
        $string = implode('', $generate);
        if ($this->math) {
            $newString = str_replace([' ', '='], '', $string);
            $strings = explode('+', $newString);
            $intA = (int) $strings[0];
            $intB = (int) $strings[1];
            $string = (string) ($intA + $intB);
        }
        return $string;
    }
}