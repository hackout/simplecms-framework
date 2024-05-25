<?php

/**
 * 判断是否Mobile访问
 *
 * @return bool
 */
function is_mobile(string $useragent): bool
{
    $clientKEY = [
        'nokia',
        'sony',
        'ericsson',
        'mot',
        'samsung',
        'htc',
        'huawei',
        'sgh',
        'lg',
        'sharp',
        'sie-',
        'philips',
        'panasonic',
        'alcatel',
        'lenovo',
        'iPhone',
        'phone',
        'ipod',
        'ipad',
        'blackberry',
        'meizu',
        'Android',
        'netfront',
        'symbian',
        'ucweb',
        'windowsce',
        'palm',
        'operamini',
        'operamobi',
        'openwave',
        'nexusone',
        'cldc',
        'midp',
        'wap',
        'mobile',
        'Weixin'
    ];
    return preg_match("/(" . implode('|', $clientKEY) . ")/i", strtolower(request()->userAgent()));
}