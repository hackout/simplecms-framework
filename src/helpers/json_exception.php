<?php

use Illuminate\Http\JsonResponse;


/**
 * 异常返回信息
 *
 * @param string $msg 消息内容
 * @param Exception|null $exception 异常Throw
 * @param string|null $jsonp JSONP标识
 * @return JsonResponse
 */
function json_exception(string $msg = 'The Request is an exception.', Exception $exception = null, string $jsonp = null): JsonResponse
{
    $result = [
        'code' => 501,
        'message' => $msg,
        'data' => [],
    ];
    if ($exception) {
        logger()->error('[MESSAGE]' . $exception->getMessage() . '[FILE]' . $exception->getFile() . '[LINE]' . $exception->getLine());
    }
    if ($jsonp) {
        return response()->json($result)->setEncodingOptions(env('JSONENCODE', 0))->withCallback($jsonp);
    }

    return response()->json($result)->setEncodingOptions(env('JSONENCODE', 0));
}