<?php

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

if (!function_exists('json_success')) {
    /**
     * 请求成功返回信息
     *
     * @param mixed $data 返回参数
     * @param string $msg 消息内容
     * @param string|null $jsonp JSONP标识
     * @return JsonResponse
     */
    function json_success($data = null, string $msg = 'successfully.', string $jsonp = null): JsonResponse
    {
        $result = [
            'code' => 200,
            'message' => $msg,
            'data' => $data,
        ];
        if ($jsonp) {
            return response()->json($result)->setEncodingOptions(env('JSONENCODE', 0))->withCallback($jsonp);
        }

        return response()->json($result)->setEncodingOptions(env('JSONENCODE', 0));
    }
}


if (!function_exists('json_error')) {
    /**
     * 错误处理返回信息
     *
     * @param string|null $msg 消息内容
     * @param array|string|null $data 附加参数
     * @param string|null $jsonp JSONP标识
     * @return JsonResponse
     */
    function json_error(string|null $msg = 'something is not incorrect.',$data = null, string $jsonp = null): JsonResponse
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
}

if (!function_exists('json_exception')) {
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
}


if (!function_exists('json_sign_in')) {
    /**
     * 用户未登录返回信息
     *
     * @param string $msg 消息内容
     * @param string|null $jsonp JSONP标识
     * @return JsonResponse
     */
    function json_sign_in(string $msg = 'You need sign in.', string $jsonp = null): JsonResponse
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
}


if (!function_exists('json_allow')) {
    /**
     * 无权限时的返回信息
     *
     * @param string $msg 消息内容
     * @param string|null $jsonp JSONP标识
     * @return JsonResponse
     */
    function json_allow(string $msg = 'The response result is permission has been failed.', string $jsonp = null): JsonResponse
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
}


if (!function_exists('json_not_found')) {
    /**
     * 404信息返回
     *
     * @param string $msg 消息内容
     * @param string|null $jsonp JSONP标识
     * @return JsonResponse
     */
    function json_not_found(string $msg = 'The page is not found.', string $jsonp = null): JsonResponse
    {
        $result = [
            'code' => 404,
            'message' => $msg,
            'data' => [],
        ];
        if ($jsonp) {
            return response()->json($result)->setEncodingOptions(env('JSONENCODE', 0))->withCallback($jsonp);
        }

        return response()->json($result)->setEncodingOptions(env('JSONENCODE', 0));
    }
}

if (!function_exists('download')) {
    /**
     * 下载文件或者图片
     *
     * @param string $file 文件路径、文件内容
     * @param string|null $name 下载保存文件名
     * @param array $headers 输出头信息
     * @return BinaryFileResponse
     */
    function download(string $file, string $name = null, array $headers = []): BinaryFileResponse
    {
        return response()->download($file, $name, $headers);
    }
}

if (!function_exists('isMobile')) {
    /**
     * 判断是否Mobile访问
     *
     * @param string $useragent 地址名
     * @param string $name 随机值
     * @return bool
     */
    function isMobile(string $useragent): bool
    {
        $clientKEY = [
            'nokia', 'sony', 'ericsson', 'mot',
            'samsung', 'htc', 'huawei', 'sgh',
            'lg', 'sharp', 'sie-', 'philips',
            'panasonic', 'alcatel', 'lenovo', 'iPhone',
            'phone', 'ipod', 'ipad', 'blackberry',
            'meizu', 'Android', 'netfront', 'symbian',
            'ucweb', 'windowsce', 'palm', 'operamini',
            'operamobi', 'openwave', 'nexusone', 'cldc',
            'midp', 'wap', 'mobile', 'Weixin'
        ];
        return preg_match("/(" . implode('|', $clientKEY) . ")/i", strtolower($useragent));
    }
}