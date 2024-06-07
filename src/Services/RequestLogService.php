<?php

namespace SimpleCMS\Framework\Services;

use App\Models\RequestLog;
use Illuminate\Http\Request;
use SimpleCMS\Framework\Enums\RequestLogEnum;
use SimpleCMS\Framework\Attributes\ApiName;
use SimpleCMS\Framework\Services\SimpleService;

class RequestLogService extends SimpleService
{
    public ?string $className = RequestLog::class;

    public function makeLog(Request $request, bool $status): void
    {
        $controllerName = $request->route()->getControllerClass();
        $actionName = $request->route()->getActionMethod();
        if (\method_exists($controllerName, $actionName)) {
            $reflectionMethod = new \ReflectionMethod($controllerName, $actionName);
            $attributes = $reflectionMethod->getAttributes(ApiName::class);
            $name = $controllerName . '@' . $actionName;
            foreach ($attributes as $attribute) {
                if ($attribute->getName() === ApiName::class) {
                    $name = $attribute->getArguments()['name'];
                }
            }
            $sql = [
                'model_id' => optional($request->user())->id,
                'model_type' => get_class(optional($request->user())->replicate()),
                'name' => $name,
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->getClientIp(),
                'method' => collect(RequestLogEnum::cases())->where('name', $request->getMethod())->value('value'),
                'url' => $request->route()->uri,
                'parameters' => $request->all(),
                'route_name' => $request->route()->getName(),
                'status' => $status
            ];
            parent::create($sql);
        }
    }
}
