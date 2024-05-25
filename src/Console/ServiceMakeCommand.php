<?php
namespace SimpleCMS\Framework\Console;


use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'create:service')]
class ServiceMakeCommand extends GeneratorCommand
{
    use WithModelStub;
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'create:service {name : The name of the controller} {--model= : The service use this model} {--type= : The service use this type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new service class';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        $type = class_basename($this->option('type'));
        return $this->resolveStubPath('/stubs/service.' . $type . '.stub');
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name)
    {
        $type = Str::studly(class_basename($this->option('type')));
        return $this->laravel->basePath() . '/App/Services/' . $type . '/' . class_basename($name) . '.php';
    }
    /**
     * Replace the namespace for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return $this
     */
    protected function replaceNamespace(&$stub, $name)
    {
        $searches = [
            ['DummyNamespace', 'DummyModelNamespace', 'NamespacedModelName'],
            ['{{ namespace }}', '{{ modelNamespace }}', '{{ modelName }}'],
            ['{{namespace}}', '{{modelNamespace}}', '{{modelName}}'],
        ];
        foreach ($searches as $search) {
            $stub = str_replace(
                $search,
                [$this->getNamespace($name), $this->modelNamespace(), $this->modelName($name)],
                $stub
            );
        }

        return $this;
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $class = class_basename(str_replace($this->getNamespace($name) . '\\', '', $name));

        return str_replace(['DummyClass', '{{ class }}', '{{class}}'], $class, $stub);
    }

    /**
     * Get the full namespace for a given class, without the class name.
     *
     * @param  string  $name
     * @return string
     */
    protected function getNamespace($name)
    {
        $type = Str::studly(class_basename($this->option('type')));
        return 'App\\Services\\' . $type;
    }

    /**
     * Get the root namespace for the class.
     *
     * @return string
     */
    protected function modelName($name)
    {
        return class_basename($this->option('model'));
    }

    /**
     * Get the root namespace for the class.
     *
     * @return string
     */
    protected function modelNamespace()
    {
        return 'App\\Models';
    }

}
