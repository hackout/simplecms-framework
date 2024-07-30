<?php
namespace SimpleCMS\Framework\Console;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'create:controller')]
class ControllerMakeCommand extends GeneratorCommand
{
    use WithModelStub;

    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'create:controller {name : The name of the controller} {--model= : The controller use this model} {--type= : The controller use this type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new controller class';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        $stub = "/stubs/controller.backend.stub";

        if ($type = $this->option('type')) {
            $stub = "/stubs/controller.{$type}.stub";
        }

        return $this->resolveStubPath($stub);
    }


    /**
     * Build the class with the given name.
     *
     * Remove the base controller import if we are already in the base namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {

        $replace = $this->option('model') ? $this->buildModelReplacements() : [];

        $class = str_replace(
            array_keys($replace),
            array_values($replace),
            parent::buildClass($name)
        );

        return $class;
    }


    /**
     * Build the model replacement values.
     *
     * @return array
     */
    protected function buildModelReplacements()
    {
        $storeService = $this->parseServiceName($this->option('model'));
        $serviceClass = $this->parseService($storeService);
        $table = Str::snake(Str::pluralStudly(class_basename($this->option('model'))));

        [$namespace, $storeRequestClass, $updateRequestClass] = [
            'Illuminate\\Http',
            'Request',
            'Request',
        ];
        $namespacedRequests = $namespace . '\\' . $storeRequestClass . ';';

        if ($storeRequestClass !== $updateRequestClass) {
            $namespacedRequests .= PHP_EOL . 'use ' . $namespace . '\\' . $updateRequestClass . ';';
        }
        $class = Str::studly(class_basename($this->option('model'))) . 'Controller';
        $controllerNamespace = $this->parseController();
        $modelName = Str::studly(class_basename($this->option('model')));
        return [
            'DummyNamespaceService' => $serviceClass,
            '{{ namespacedService }}' => $serviceClass,
            '{{namespacedService}}' => $serviceClass,
            'DummyStoreService' => $storeService,
            '{{ storeService }}' => $storeService,
            '{{storeService}}' => $storeService,
            'DummyControllerNamespace' => $controllerNamespace,
            '{{ controllerNamespace }}' => $controllerNamespace,
            '{{controllerNamespace}}' => $controllerNamespace,
            'DummyTable' => $table,
            '{{ table }}' => $table,
            '{{table}}' => $table,
            'DummyModelName' => $modelName,
            '{{ modelName }}' => $modelName,
            '{{modelName}}' => $modelName,
            'DummyClass' => $class,
            '{{ class }}' => $class,
            '{{class}}' => $class,
            '{{ storeRequest }}' => $storeRequestClass,
            '{{storeRequest}}' => $storeRequestClass,
            '{{ updateRequest }}' => $updateRequestClass,
            '{{updateRequest}}' => $updateRequestClass,
            '{{ namespacedStoreRequest }}' => $namespace . '\\' . $storeRequestClass,
            '{{namespacedStoreRequest}}' => $namespace . '\\' . $storeRequestClass,
            '{{ namespacedUpdateRequest }}' => $namespace . '\\' . $updateRequestClass,
            '{{namespacedUpdateRequest}}' => $namespace . '\\' . $updateRequestClass,
            '{{ namespacedRequests }}' => $namespacedRequests,
            '{{namespacedRequests}}' => $namespacedRequests,
        ];
    }

    /**
     * Get the fully-qualified model class name.
     *
     * @param  string  $model
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function parseServiceName($model)
    {
        if (preg_match('([^A-Za-z0-9_/\\\\])', $model)) {
            throw new InvalidArgumentException('Model name contains invalid characters.');
        }
        $name = class_basename(str_replace('\\', '/', Str::replaceFirst($this->rootNamespace(), '', $model)));
        return $name . 'Service';
    }

    /**
     * Get the fully-qualified model class name.
     *
     * @param  string  $model
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function parseService($model)
    {
        $type = Str::studly(class_basename($this->option('type')));

        $name = class_basename(str_replace('\\', '/', Str::replaceFirst($this->rootNamespace(), '', $model)));
        return 'App\\Services\\' . $type . '\\' . $name;

    }

    protected function parseController()
    {
        $type = Str::studly(class_basename($this->option('type')));
        return 'Http\\Controllers\\' . $type;
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name)
    {
        $name = class_basename($name);
        $type = Str::studly(class_basename((string) $this->option('type')));
        return $this->laravel->basePath() . '/App/Http/Controllers/' . $type . '/' . $name . '.php';
    }

}
