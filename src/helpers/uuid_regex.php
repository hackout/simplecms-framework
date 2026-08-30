<?php

/**
 * UUID Regex
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 * @return string
 */
function uuid_regex(): string
{
    return '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}';
}