<?php
namespace SimpleCMS\Framework\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'create:migration')]
class MigrateMakeCommand extends GeneratorCommand
{

    use WithModelStub;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'create:migration {name : The name of the migration}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new create migration';


    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->resolveStubPath('/stubs/migration.create.stub');
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return is_dir(app_path('Models')) ? $rootNamespace . '\\Models' : $rootNamespace;
    }

    /**
     * Get the date prefix for the migration.
     *
     * @return string
     */
    protected function getDatePrefix()
    {
        return date('Y_m_d_His');
    }

    protected function getPath($name)
    {
        return $this->laravel->databasePath() . '/migrations/' . $this->getDatePrefix() . '_' . class_basename($name) . '.php';
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
        $table = str_replace(['create_', '_table'], '', $this->argument('name'));
        $searches = [
            ['DummyNamespace', 'DummyRootNamespace', 'NamespacedDummyUserModel', 'DummyTable'],
            ['{{ namespace }}', '{{ rootNamespace }}', '{{ namespacedUserModel }}', '{{ table }}'],
            ['{{namespace}}', '{{rootNamespace}}', '{{namespacedUserModel}}', '{{table}}'],
        ];

        foreach ($searches as $search) {
            $stub = str_replace(
                $search,
                [$this->getNamespace($name), $this->rootNamespace(), $this->userProviderModel(), $table],
                $stub
            );
        }

        return $this;
    }
}
