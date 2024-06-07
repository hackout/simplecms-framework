<?php

namespace SimpleCMS\Framework\Enums;

enum RequestLogEnum: int
{
    case GET = 1;
    case POST = 2;
    case PUT = 3;
    case PATCH = 4;
    case DELETE = 5;
}
