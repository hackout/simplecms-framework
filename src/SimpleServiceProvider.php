<?php

namespace SimpleCMS\Framework;

use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use SimpleCMS\Framework\Console\ModelMakeCommand;
use SimpleCMS\Framework\Packages\Captcha\Captcha;
use SimpleCMS\Framework\Validation\Rule\PhoneRule;
use SimpleCMS\Framework\Console\SeederMakeCommand;
use SimpleCMS\Framework\Console\MigrateMakeCommand;
use SimpleCMS\Framework\Console\ServiceMakeCommand;
use SimpleCMS\Framework\Validation\Rule\IDCardRule;
use SimpleCMS\Framework\Validation\Rule\MobileRule;
use SimpleCMS\Framework\Validation\Rule\TelephoneRule;
use SimpleCMS\Framework\Console\ControllerMakeCommand;

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
        $this->bindCaptcha();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
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
        $this->bootConfig();
        $this->loadedHelpers();
        $this->bootDefaultDisk();
        $this->loadedValidator();
        
        // HTTP routing
        if(!config('cms.captcha.disable')){
            $router = $this->app['router'];
            $router->get('captcha/api/{config?}', '\SimpleCMS\Framework\Http\Controllers\CaptchaController@getCaptchaApi');
            $router->get('captcha/{config?}', '\SimpleCMS\Framework\Http\Controllers\CaptchaController@getCaptcha');
        }
    }

    protected function bindCaptcha(): void
    {

        // Bind captcha
        $this->app->bind('captcha', function ($app) {
            return new Captcha(
                $app['Illuminate\Filesystem\Filesystem'],
                $app['Illuminate\Contracts\Config\Repository'],
                $app['Illuminate\Session\Store'],
                $app['Illuminate\Hashing\BcryptHasher'],
                $app['Illuminate\Support\Str']
            );
        });
    }

    /**
     * 加载验证
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return void
     */
    protected function loadedValidator(): void
    {
        Validator::extend(
            'id_card',
            IDCardRule::class
        );
        Validator::extend(
            'mobile',
            MobileRule::class
        );
        Validator::extend(
            'telephone',
            TelephoneRule::class
        );
        Validator::extend(
            'phone',
            PhoneRule::class
        );
        Validator::extend(
            'captcha',
            function ($attribute, $value, $parameters) {
                return config('cms.captcha.disable') || ($value && captcha_check($value));
            }
        );
        Validator::extend(
            'captcha_api',
            function ($attribute, $value, $parameters) {
                return config('cms.captcha.disable') || ($value && captcha_api_check($value, $parameters[0], $parameters[1] ?? 'default'));
            }
        );
    }

    /**
     * 加载辅助函数
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return void
     */
    protected function loadedHelpers(): void
    {

        foreach (scandir(__DIR__ . DIRECTORY_SEPARATOR . 'helpers') as $helperFile) {
            $path = sprintf(
                '%s%s%s%s%s',
                __DIR__,
                DIRECTORY_SEPARATOR,
                'helpers',
                DIRECTORY_SEPARATOR,
                $helperFile
            );

            if (!is_file($path)) {
                continue;
            }

            $function = Str::before($helperFile, '.php');

            if (function_exists($function)) {
                continue;
            }

            require_once $path;
        }
    }


    /**
     * 创建默认目录
     * @return void
     */
    protected function bootDefaultDisk(): void
    {
        if (!is_dir(app_path('Services'))) {
            @mkdir(app_path('Services'), 0755);
        }
        if (!is_dir(app_path('Http/Controllers/Backend'))) {
            @mkdir(app_path('Http/Controllers/Backend'), 0755);
        }
        if (!is_dir(app_path('Http/Controllers/Frontend'))) {
            @mkdir(app_path('Http/Controllers/Frontend'), 0755);
        }
        if (!is_dir(app_path('Services/Backend'))) {
            @mkdir(app_path('Services/Backend'), 0755);
        }
        if (!is_dir(app_path('Services/Frontend'))) {
            @mkdir(app_path('Services/Frontend'), 0755);
        }
        if (!is_dir(app_path('Services/Private'))) {
            @mkdir(app_path('Services/Private'), 0755);
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
