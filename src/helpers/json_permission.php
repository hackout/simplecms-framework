<?php

use Illuminate\Http\JsonResponse;


/**
 * 无权限时的返回信息
 *
 * @param string $msg 消息内容
 * @param string|null $jsonp JSONP标识
 * @return JsonResponse
 */
function json_permission(string $msg = 'The response result is permission has been failed.', string $jsonp = null): JsonResponse
{
    $result = [
        'code' => 401,
        'message' => $msg,
        'data' => [],
    ];
    if ($jsonp) {
        return response()->json($result)->setEncodingOptions(env('JSONENCODE', 0))->withCallback($jsonp);
    }

    return response()->json($result)->setEncodingOptions(env('JSONENCODE', 0));
}