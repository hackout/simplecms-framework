<?php
namespace SimpleCMS\Framework\Validation;

/**
 * 身份证验证类
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 */
class IDCard
{
    protected $id;

    protected $code = '';

    protected $province = '';

    /**
     * 系数
     * @var int[]
     */
    protected $coefficient = [
        7,
        9,
        10,
        5,
        8,
        4,
        2,
        1,
        6,
        3,
        7,
        9,
        10,
        5,
        8,
        4,
        2
    ];

    /**
     * 余数列表
     * @var array
     */
    protected $remainder = [
        1,
        0,
        'X',
        9,
        8,
        7,
        6,
        5,
        4,
        3,
        2
    ];

    /**
     * IDCard constructor.
     * @param $id
     */
    public function __construct($id)
    {
        $this->setId($id);
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = strtoupper($id);
    }

    /**
     * 身份证是否有效
     * @return bool
     */
    public function isValid(): bool
    {
        if (strlen($this->id) != 18) {
            return false;
        }

        $provinces = $this->getProvinceCode();
        $province = $this->getProvince();
        $remainder = $this->getRemainder();
        $CRC = $this->getCRC();

        if (in_array($province, $provinces) && $CRC == $this->remainder[$remainder]) {
            return true;
        }

        return false;
    }

    /**
     * 获取行政区划代码
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return array
     */
    protected function getProvinceCode(): array
    {
        return [
            '11',
            '12',
            '13',
            '14',
            '15',
            '21',
            '22',
            '23',
            '31',
            '32',
            '33',
            '34',
            '35',
            '36',
            '37',
            '41',
            '42',
            '43',
            '44',
            '45',
            '46',
            '50',
            '51',
            '52',
            '53',
            '54',
            '55',
            '61',
            '62',
            '63',
            '64',
            '65',
            '81',
            '82',
            '83'
        ];
    }

    /**
     * 获取省级行政区划
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return string
     */
    public function getProvince():string
    {
        if (!$this->code) {
            $this->province = substr($this->id, 0, 2);
        }
        return $this->province;
    }

    /**
     * 获取行政代码
     * @return string
     */
    public function getCode(): string
    {
        if (!$this->code) {
            $this->code = substr($this->id, 0, 6);
        }

        return $this->code;
    }

    /**
     * 获取生日
     * @return string
     */
    public function getBirthday(): string
    {
        return substr($this->id, 6, 8);
    }

    /**
     * 获取顺序码
     * @return string
     */
    protected function getSequenceCode(): string
    {
        return substr($this->id, 14, 3);
    }

    /**
     * 获取校验码
     * @return string
     */
    protected function getCRC(): string
    {
        return substr($this->id, 17, 1);
    }

    /**
     * 从身份证号中获取性别
     * @return int
     */
    public function getGender(): int
    {
        $sexBit = intval(substr($this->id, 16, 1));
        return is_float($sexBit / 2) ? 1 : 0;
    }

    /**
     * 计算校验码
     * @return int
     */
    protected function getRemainder(): int
    {
        $sum = 0;
        $length = 17;
        $data = substr($this->id, 0, $length);

        for ($i = 0; $i < $length; $i++) {
            $sum += intval($data[$i]) * $this->coefficient[$i];
        }

        return $sum % 11;
    }

    /**
     * 获取省和市的行政代码
     * @return string[]
     */
    protected function getProvinceAndCityCode(): array
    {
        $code = $this->getCode();

        $cityCode = substr($code, 0, 4) . '00';
        $provinceCode = substr($code, 0, 2) . '0000';

        return [$provinceCode, $cityCode];
    }
}