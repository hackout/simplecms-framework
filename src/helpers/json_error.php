<?php

use Illuminate\Http\JsonResponse;

/**
 * 错误处理返回信息
 *
 * @param string|null $msg 消息内容
 * @param array|string|null $data 附加参数
 * @param string|null $jsonp JSONP标识
 * @return JsonResponse
 */
function json_error(string|null $msg = 'something is not incorrect.', $data = null, string $jsonp = null): JsonResponse
{
    $result = [
        'code' => 500,
        'message' => $msg,
        'data' => $data,
    ];
    if ($jsonp) {
        return response()->json($result)->setEncodingOptions(env('JSONENCODE', 0))->withCallback($jsonp);
    }

    return response()->json($result)->setEncodingOptions(env('JSONENCODE', 0));
}