<?php

/**
 * 判断是否为移动设备访问
 *
 * @param string $useragent 用户代理字符串
 * @return bool
 */
function is_mobile(string $useragent): bool
{
    // 定义移动设备关键词数组
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

    // 使用正则表达式匹配用户代理字符串是否包含移动设备关键词
    return (bool) preg_match("/(" . implode('|', $clientKEY) . ")/i", strtolower($useragent));
}