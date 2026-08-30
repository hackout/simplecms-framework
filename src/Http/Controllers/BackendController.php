<?php

namespace SimpleCMS\Framework\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use SimpleCMS\Framework\Services\RequestLogService;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BackendController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * 返回正常数据
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param array|string|null|bool|Collection $data 数据
     * @param string $message 说明消息
     * @return JsonResponse
     */
    public function success(array|string|null|bool|Collection $data = null, $message = 'success'): JsonResponse
    {
        (new RequestLogService())->makeLog(request(), true);
        return json_success($data, $message);
    }

    /**
     * 返回失败数据
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param string $message
     * @param array|string|null|bool|Collection $data
     * @return JsonResponse
     */
    public function error(string $message = 'error', array|string|null|bool|Collection $data = null): JsonResponse
    {
        (new RequestLogService())->makeLog(request(), false);
        return json_error($message, $data);
    }
}
