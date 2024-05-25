<?php

use SimpleCMS\Framework\Validation\IDCard;

/**
 * 通过身份证查询行政区划代码
 * 
 * @description 证件行政区划代码并不一定是正确的行政代码，仅代表证件录入时所处的代码 
 * 
 * @author Dennis Lui <hackout@vip.qq.com>
 *
 * @param string $id 身份证号码
 * @return string 
 */
function id_code(string $id): string
{
    return (new IDCard($id))->getCode();
}