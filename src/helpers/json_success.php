<?php

use Illuminate\Http\JsonResponse;

/**
 * 请求成功返回信息
 *
 * @param mixed $data 返回参数
 * @param string $msg 消息内容
 * @param string|null $jsonp JSONP标识
 * @return JsonResponse
 */
function json_success($data = null, string $msg = 'successfully.', ?string $jsonp = null): JsonResponse
{
    $result = [
        'code' => 200,
        'message' => $msg,
        'data' => $data,
    ];

    $response = response()->json($result)->setEncodingOptions(env('JSONENCODE', 0));

    return is_string($jsonp) && trim($jsonp) !== '' ? $response->withCallback(trim($jsonp)) : $response;
}