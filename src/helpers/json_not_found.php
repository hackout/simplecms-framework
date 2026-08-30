<?php

use Illuminate\Http\JsonResponse;

/**
 * 404信息返回
 *
 * @param string $msg 消息内容
 * @param string|null $jsonp JSONP标识
 * @return JsonResponse
 */
function json_not_found(string $msg = 'The page is not found.', ?string $jsonp = null): JsonResponse
{
    $result = [
        'code' => 404,
        'message' => $msg,
        'data' => [],
    ];

    $response = response()->json($result)->setEncodingOptions(env('JSONENCODE', 0));

    return is_string($jsonp) && trim($jsonp) !== '' ? $response->withCallback(trim($jsonp)) : $response;
}