<?php

use Illuminate\Http\JsonResponse;


/**
 * 用户未登录返回信息
 *
 * @param string $msg 消息内容
 * @param string|null $jsonp JSONP标识
 * @return JsonResponse
 */
function json_redirect(string $msg = 'You need sign in.', string $jsonp = null): JsonResponse
{
    $result = [
        'code' => 302,
        'message' => $msg,
        'data' => [],
    ];
    if ($jsonp) {
        return response()->json($result)->setEncodingOptions(env('JSONENCODE', 0))->withCallback($jsonp);
    }

    return response()->json($result)->setEncodingOptions(env('JSONENCODE', 0));
}