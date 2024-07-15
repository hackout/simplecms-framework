<?php
namespace SimpleCMS\Framework\Services;

use Illuminate\Http\Request;
use SimpleCMS\Framework\Models\RequestLog;
use SimpleCMS\Framework\Attributes\ApiName;
use SimpleCMS\Framework\Enums\RequestLogEnum;
use SimpleCMS\Framework\Services\SimpleService;

class RequestLogService extends SimpleService
{
    public ?string $className = RequestLog::class;

    public function makeLog(Request $request, bool $status): void
    {
        $controllerName = $request->route()->getControllerClass();
        $actionName = $request->route()->getActionMethod();

        if (\method_exists($controllerName, $actionName)) {

            try {
                $reflectionMethod = new \ReflectionMethod($controllerName, $actionName);
                $attributes = $reflectionMethod->getAttributes(ApiName::class);
                $name = $controllerName . '@' . $actionName;

                foreach ($attributes as $attribute) {

                    if ($attribute->getName() === ApiName::class) {
                        $name = $attribute->getArguments()['name'];
                    }
                }
                $model_type = $request->user() ? get_class($request->user()->replicate()) : null;
                $sql = [
                    'model_id' => optional($request->user())->id,
                    'model_type' => $model_type,
                    'name' => $name,
                    'user_agent' => $request->userAgent(),
                    'ip_address' => $request->getClientIp(),
                    'method' => RequestLogEnum::getValue($request->getMethod())->value,
                    'url' => $request->route()->uri,
                    'parameters' => $request->all(),
                    'route_name' => $request->route()->getName(),
                    'status' => $status
                ];

                parent::create($sql);
            } catch (\Exception $exception) {
                error_log($exception->getMessage());
            }
        }
    }
}
