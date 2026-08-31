<?php
namespace SimpleCMS\Framework\Services;

use function get_class;
use function method_exists;
use Illuminate\Http\Request;
use SimpleCMS\Framework\Models\RequestLog;
use SimpleCMS\Framework\Attributes\ApiName;
use SimpleCMS\Framework\Enums\RequestLogEnum;
use SimpleCMS\Framework\Services\SimpleService;

class RequestLogService extends SimpleService
{
    protected string $className = RequestLog::class;

    public function makeLog(Request $request, bool $status): void
    {
        $controllerName = optional($request->route())->getControllerClass();
        $actionName = optional($request->route())->getActionMethod();
        if (!empty($controllerName) && !empty($actionName)) {
            $this->applyLog($request, $status, $controllerName, $actionName);
        }
    }

    private function applyLog(Request $request, bool $status, string $controllerName, string $actionName): void
    {
        if (method_exists($controllerName, $actionName)) {
            $reflectionClass = new \ReflectionClass($controllerName);
            $className = null;
            foreach ($reflectionClass->getAttributes(ApiName::class) as $attribute) {
                if ($attribute->getName() === ApiName::class) {
                    $className = $attribute->getArguments()['name'] ?? null;
                    break;
                }
            }

            $reflectionMethod = new \ReflectionMethod($controllerName, $actionName);
            $methodName = null;
            foreach ($reflectionMethod->getAttributes(ApiName::class) as $attribute) {
                if ($attribute->getName() === ApiName::class) {
                    $methodName = $attribute->getArguments()['name'] ?? null;
                    break;
                }
            }

            $defaultName = $controllerName . '@' . $actionName;
            $name = $defaultName;

            if (!empty($className) && !empty($methodName)) {
                $name = $className . ' : ' . $methodName;
            } elseif (!empty($className)) {
                $name = $className;
            } elseif (!empty($methodName)) {
                $name = $defaultName . ' : ' . $methodName;
            }

            $sql = $this->makeSql($request, $name, $status);

            parent::create($sql);
        }
    }

    private function makeSql(Request $request, string|null $name = null, bool $status = false): array
    {
        $model_type = !empty($request->user()) ? get_class($request->user()->replicate()) : null;
        return [
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
    }
}
