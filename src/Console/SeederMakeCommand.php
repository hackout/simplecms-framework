<?php
namespace SimpleCMS\Framework\Console;


use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'create:seeder')]
class SeederMakeCommand extends GeneratorCommand
{
    use WithModelStub;
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'create:seeder {name : The name of the seeder} {--model= : The seeder use this model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new seeder class';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->resolveStubPath('/stubs/seeder.stub');
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
            ['DummyNamespace', 'DummyRootNamespace', 'NamespacedDummyUserModel', 'ModelClass'],
            ['{{ namespace }}', '{{ rootNamespace }}', '{{ namespacedUserModel }}', '{{ modelClass }}'],
            ['{{namespace}}', '{{rootNamespace}}', '{{namespacedUserModel}}', '{{modelClass}}'],
        ];

        foreach ($searches as $search) {
            $stub = str_replace(
                $search,
                [$this->getNamespace($name), $this->rootNamespace(), $this->userProviderModel(), $this->getModelClass()],
                $stub
            );
        }

        return $this;
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name)
    {
        $name = str_replace('\\', '/', Str::replaceFirst($this->rootNamespace(), '', $name));

        if (is_dir($this->laravel->databasePath() . '/seeds')) {
            return $this->laravel->databasePath() . '/seeds/' . $name . '.php';
        }

        return $this->laravel->databasePath() . '/seeders/' . $name . '.php';
    }

    /**
     * Get the root namespace for the class.
     *
     * @return string
     */
    protected function rootNamespace()
    {
        return 'Database\Seeders\\';
    }


    /**
     * Get the model for the default guard's user provider.
     *
     * @return string|null
     */
    protected function getModelClass()
    {
        return class_basename($this->option('model'));
    }

    /**
     * Get the model for the default guard's user provider.
     *
     * @return string|null
     */
    protected function userProviderModel()
    {
        return $this->option('model');
    }


    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['--model', null, InputOption::VALUE_NONE, '应用的模型'],
        ];
    }
}
