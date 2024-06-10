<?php
namespace SimpleCMS\Framework\Packages\ExcelPlus;

use SimpleCMS\Framework\Exceptions\SimpleException;

class Convert
{

    public function numberToLetter(int|float|string $number): string
    {
        $letters = range('A', 'Z');
        $num = count($letters);
        $maxLength = pow($num, 3);
        if ($number > $maxLength) {
            throw new SimpleException(trans("simplecms::excel_plus.convert.outsize", ['max' => $maxLength]));
        }
        $strings = [];
        if (intval($number / $num) > $num) {
            $strings[] = intval(intval($number / $num) / $num);
        } else {
            $strings[] = 0;
        }
        if ($number > $num) {
            $strings[] = intval($number / $num) % $num;
        } else {
            $strings[] = 0;
        }
        $strings[] = $number % $num;
        if (!intval(implode('', $strings))) {
            $strings[0] = 26;
        }
        $result = '';
        foreach ($strings as $key) {
            if ($key) {
                $result .= $letters[$key - 1];
            }
        }
        return $result;
    }

    /**
     * Hash颜色转ExcelColor
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  string $string
     * @return string
     */
    public function hashToColor(string $string): string
    {
        return strtoupper(str_replace('#', '', $string));
    }

    /**
     * PX转PT
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  integer|float|string $px
     * @return float|string|integer
     */
    public function pxToPt(int|float|string $px): float|string|int
    {
        return $px * (72 / 96);
    }
}
