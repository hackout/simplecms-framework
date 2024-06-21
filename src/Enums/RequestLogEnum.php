<?php

namespace SimpleCMS\Framework\Enums;

enum RequestLogEnum: int
{
    case GET = 1;
    case POST = 2;
    case PUT = 3;
    case PATCH = 4;
    case DELETE = 5;

    public static function fromValue(int $value): self
    {
        return match ($value) {
            1 => self::GET,
            2 => self::POST,
            3 => self::PUT,
            4 => self::PATCH,
            5 => self::DELETE,
            default => self::GET
        };
    }
}
