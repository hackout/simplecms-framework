<?php

namespace SimpleCMS\Framework;

use Illuminate\Support\ServiceProvider;
use SimpleCMS\Framework\Console\MigrateMakeCommand;
use SimpleCMS\Framework\Console\SeederMakeCommand;
use SimpleCMS\Framework\Console\ServiceMakeCommand;
use SimpleCMS\Framework\Console\ControllerMakeCommand;
use SimpleCMS\Framework\Console\ModelMakeCommand;

class SimpleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        if (!app()->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__ . '/../config/cms.php', 'cms');
        }
        $this->commands([
            SeederMakeCommand::class,
            MigrateMakeCommand::class,
            ServiceMakeCommand::class,
            ControllerMakeCommand::class,
            ModelMakeCommand::class
        ]);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->bootDefaultDisk();
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/Console/stubs/controller.backend.stub' => base_path('stubs/controller.backend.stub'),
                __DIR__ . '/Console/stubs/controller.frontend.stub' => base_path('stubs/controller.frontend.stub'),
                __DIR__ . '/Console/stubs/migration.create.stub' => base_path('stubs/migration.create.stub'),
                __DIR__ . '/Console/stubs/model.stub' => base_path('stubs/model.stub'),
                __DIR__ . '/Console/stubs/seeder.stub' => base_path('stubs/seeder.stub'),
                __DIR__ . '/Console/stubs/service.backend.stub' => base_path('stubs/service.backend.stub'),
                __DIR__ . '/Console/stubs/service.frontend.stub' => base_path('stubs/service.frontend.stub'),
                __DIR__ . '/Console/stubs/service.private.stub' => base_path('stubs/service.private.stub'),
            ], 'stubs');
        }
    }


    /**
     * 创建默认目录
     * @return void
     */
    protected function bootDefaultDisk(): void
    {
        if (!is_dir(app_path('Services'))) {
            mkdir(app_path('Services'), 0755);
        }
        if (!is_dir(app_path('Http/Controllers/Backend'))) {
            mkdir(app_path('Http/Controllers/Backend'), 0755);
        }
        if (!is_dir(app_path('Http/Controllers/Frontend'))) {
            mkdir(app_path('Http/Controllers/Frontend'), 0755);
        }
        if (!is_dir(app_path('Services/Backend'))) {
            mkdir(app_path('Services/Backend'), 0755);
        }
        if (!is_dir(app_path('Services/Frontend'))) {
            mkdir(app_path('Services/Frontend'), 0755);
        }
        if (!is_dir(app_path('Services/Private'))) {
            mkdir(app_path('Services/Private'), 0755);
        }
    }


    /**
     * 初始化配置文件
     * @return void
     */
    protected function bootConfig(): void
    {
        $this->publishes([
            __DIR__ . '/../config/cms.php' => config_path('cms.php'),
        ], 'cms');
    }
}
