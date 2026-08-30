<?php
namespace SimpleCMS\Framework\Console;


use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'create:route')]
class RouteMakeCommand extends GeneratorCommand
{
    use WithModelStub;
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'create:route {name : The name of the route} {--type= : The service use this type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new route file';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        $type = class_basename($this->option('type'));
        return $this->resolveStubPath('/stubs/route.' . $type . '.stub');
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name)
    {
        $type = strtolower(class_basename($this->option('type')));
        return $this->laravel->basePath() . '/routes/' . $type . '/' . $this->getPrefixName($name) . '.php';
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
            ['DummyControllerNamespace', 'DummyPrefixName', 'DummyControllerName'],
            ['{{ controllerNamespace }}', '{{ prefixName }}', '{{ controllerName }}'],
            ['{{controllerNamespace}}', '{{prefixName}}', '{{controllerName}}'],
        ];
        foreach ($searches as $search) {
            $stub = str_replace(
                $search,
                [$this->getControllerNamespace($name), $this->getPrefixName($name), $this->getControllerName($name)],
                $stub
            );
        }

        return $this;
    }


    /**
     * Get the full namespace for a given class, without the class name.
     *
     * @param  string  $name
     * @return string
     */
    protected function getControllerNamespace($name)
    {
        $type = Str::studly(class_basename($this->option('type')));
        return 'App\\Http\\Controllers\\' . $type . '\\' . $this->getControllerName($name);
    }

    /**
     * Get the root namespace for the class.
     *
     * @param  string  $name
     * @return string
     */
    protected function getControllerName($name)
    {
        return Str::studly(class_basename($name)) . 'Controller';
    }

    /**
     * Get the root namespace for the class.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPrefixName($name)
    {
        return Str::snake(class_basename($name));
    }

}
