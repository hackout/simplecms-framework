<?php
namespace SimpleCMS\Framework\Console;

use Illuminate\Support\Str;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'create:model')]
class ModelMakeCommand extends GeneratorCommand
{

    use WithModelStub;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'create:model';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new SimpleCMS model class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Model';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (parent::handle() === false && !$this->option('force')) {
            return;
        }
        $this->createSeeder();
        $this->createMigration();
        $this->createBackendController();
        $this->createFrontendController();
        $this->createBackendService();
        $this->createFrontendService();
        $this->createPrivateService();
        $this->createBackendRoute();
        $this->createFrontendRoute();
        $this->createEnums();

    }


    /**
     * Create a migration file for the model.
     *
     * @return void
     */
    protected function createMigration()
    {
        $table = Str::snake(Str::pluralStudly(class_basename($this->argument('name'))));

        $this->call('create:migration', [
            'name' => "create_{$table}_table"
        ]);
    }

    /**
     * Create a enums file for the model.
     *
     * @return void
     */
    protected function createEnums()
    {
        $name = Str::studly(class_basename($this->argument('name')));

        $this->call('make:enum', [
            'name' => $name,
            '-i'
        ]);
    }
    /**
     * Create a seeder file for the model.
     *
     * @return void
     */
    protected function createSeeder()
    {
        $seeder = Str::studly(class_basename($this->argument('name')));
        $modelName = $this->qualifyClass($this->getNameInput());

        $this->call('create:seeder', [
            'name' => "{$seeder}Seeder",
            '--model' => $modelName,
        ]);
    }

    /**
     * Create a route for the model.
     *
     * @return void
     */
    protected function createBackendRoute()
    {
        $route = Str::studly(class_basename($this->argument('name')));

        $this->call('create:route', array_filter([
            'name' => "{$route}",
            '--type' => 'backend'
        ]));
    }
    /**
     * Create a route for the model.
     *
     * @return void
     */
    protected function createFrontendRoute()
    {
        $route = Str::studly(class_basename($this->argument('name')));

        $this->call('create:route', array_filter([
            'name' => "{$route}",
            '--type' => 'frontend'
        ]));
    }
    /**
     * Create a controller for the model.
     *
     * @return void
     */
    protected function createBackendController()
    {
        $controller = Str::studly(class_basename($this->argument('name')));

        $modelName = $this->qualifyClass($this->getNameInput());

        $this->call('create:controller', array_filter([
            'name' => "{$controller}Controller",
            '--model' => $modelName,
            '--type' => 'backend'
        ]));
    }

    /**
     * Create a controller for the model.
     *
     * @return void
     */
    protected function createFrontendController()
    {
        $controller = Str::studly(class_basename($this->argument('name')));

        $modelName = $this->qualifyClass($this->getNameInput());

        $this->call('create:controller', array_filter([
            'name' => "{$controller}Controller",
            '--model' => $modelName,
            '--type' => 'frontend'
        ]));
    }
    /**
     * Create a service for the model.
     *
     * @return void
     */
    protected function createBackendService()
    {
        $service = Str::studly(class_basename($this->argument('name')));

        $modelName = $this->qualifyClass($this->getNameInput());

        $this->call('create:service', array_filter([
            'name' => "{$service}Service",
            '--model' => $modelName,
            '--type' => 'backend'
        ]));
    }
    /**
     * Create a service for the model.
     *
     * @return void
     */
    protected function createFrontendService()
    {
        $service = Str::studly(class_basename($this->argument('name')));

        $modelName = $this->qualifyClass($this->getNameInput());

        $this->call('create:service', array_filter([
            'name' => "{$service}Service",
            '--model' => $modelName,
            '--type' => 'frontend'
        ]));
    }
    /**
     * Create a service for the model.
     *
     * @return void
     */
    protected function createPrivateService()
    {
        $service = Str::studly(class_basename($this->argument('name')));

        $modelName = $this->qualifyClass($this->getNameInput());

        $this->call('create:service', array_filter([
            'name' => "{$service}Service",
            '--model' => $modelName,
            '--type' => 'private'
        ]));
    }


    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->resolveStubPath('/stubs/model.stub');
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

}
