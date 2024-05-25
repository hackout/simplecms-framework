<?php

use SimpleCMS\Framework\Validation\IDCard;

/**
 * 通过身份证查询性别
 * @author Dennis Lui <hackout@vip.qq.com>
 *
 * @param string $id 身份证号码
 * @return int  0=Female,1=Male
 */
function id_gender(string $id): int
{
    return (new IDCard($id))->getGender();
}