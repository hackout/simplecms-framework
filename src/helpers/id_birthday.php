<?php

use SimpleCMS\Framework\Validation\IDCard;

/**
 * 通过身份证查询生日
 * @author Dennis Lui <hackout@vip.qq.com>
 *
 * @param string $id 身份证号码
 * @return string 
 */
function id_birthday(string $id): string
{
    return (new IDCard($id))->getBirthday();
}