<?php

namespace SimpleCMS\Framework;

use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use SimpleCMS\Framework\Console\ModelMakeCommand;
use SimpleCMS\Framework\Console\RouteMakeCommand;
use SimpleCMS\Framework\Packages\Captcha\Captcha;
use SimpleCMS\Framework\Console\SeederMakeCommand;
use SimpleCMS\Framework\Validation\Rule\PhoneRule;
use SimpleCMS\Framework\Console\MigrateMakeCommand;
use SimpleCMS\Framework\Console\ServiceMakeCommand;
use SimpleCMS\Framework\Validation\Rule\IDCardRule;
use SimpleCMS\Framework\Validation\Rule\MobileRule;
use SimpleCMS\Framework\Validation\Rule\ChineseRule;
use SimpleCMS\Framework\Console\SimpleCMSInitCommand;
use SimpleCMS\Framework\Http\Middleware\LoadLanguage;
use SimpleCMS\Framework\Console\ControllerMakeCommand;
use SimpleCMS\Framework\Validation\Rule\CarNumberRule;
use SimpleCMS\Framework\Validation\Rule\CompanyIDRule;
use SimpleCMS\Framework\Validation\Rule\TelephoneRule;
use SimpleCMS\Framework\Http\Middleware\CheckPermission;
use SimpleCMS\Framework\Validation\Rule\ChinesePostCodeRule;

class SimpleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->commands([
            SeederMakeCommand::class,
            MigrateMakeCommand::class,
            ServiceMakeCommand::class,
            ControllerMakeCommand::class,
            ModelMakeCommand::class,
            RouteMakeCommand::class,
            SimpleCMSInitCommand::class,
        ]);
        $this->registerMiddleware();
    }

    /**
     * 注册Middleware
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return void
     */
    protected function registerMiddleware(): void
    {
        $this->app->singleton(CheckPermission::class);
        $this->app->singleton(LoadLanguage::class);
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
                __DIR__ . '/Console/stubs/route.backend.stub' => base_path('stubs/route.backend.stub'),
                __DIR__ . '/Console/stubs/route.frontend.stub' => base_path('stubs/route.frontend.stub'),
            ], 'stubs');
        }
        $this->bootConfig();
        $this->loadedHelpers();
        $this->bootDefaultDisk();
        $this->loadedValidator();
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'simplecms');
        $this->loadRoutes();
        $this->loadFacades();
        $this->bindObservers();
    }

    /**
     * 加载模型事件
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return void
     */
    protected function bindObservers(): void
    {
        \SimpleCMS\Framework\Models\Menu::observe(\SimpleCMS\Framework\Observers\MenuObserver::class);
        \SimpleCMS\Framework\Models\Role::observe(\SimpleCMS\Framework\Observers\RoleObserver::class);
        \SimpleCMS\Framework\Models\Dict::observe(\SimpleCMS\Framework\Observers\DictObserver::class);
    }

    /**
     * 绑定Facades
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return void
     */
    protected function loadFacades(): void
    {
        $this->app->bind('captcha', function ($app) {
            return new Captcha(
                $app['Illuminate\Filesystem\Filesystem'],
                $app['Illuminate\Contracts\Config\Repository'],
                $app['Illuminate\Session\Store'],
                $app['Illuminate\Hashing\BcryptHasher'],
                $app['Illuminate\Support\Str']
            );
        });
        $this->app->bind('system_config', \SimpleCMS\Framework\Packages\System\Config::class);
        $this->app->bind('system_info', \SimpleCMS\Framework\Packages\System\System::class);
        $this->app->bind('excel_convert', \SimpleCMS\Framework\Packages\ExcelPlus\Convert::class);
        $this->app->bind('cache_manage', \SimpleCMS\Framework\Packages\System\Cache::class);
        $this->app->bind('dict', \SimpleCMS\Framework\Packages\Dict\Dict::class);
        $this->app->bind('menu', \SimpleCMS\Framework\Packages\Menu\Menu::class);
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
            'chinese',
            ChineseRule::class
        );
        Validator::extend(
            'car_number',
            CarNumberRule::class
        );
        Validator::extend(
            'company_id',
            CompanyIDRule::class
        );
        Validator::extend(
            'chinese_postcode',
            ChinesePostCodeRule::class
        );
        Validator::extend(
            'image_or_url',
            function ($attribute, $value, $parameters) {
                if (Str::isUrl($value) || strpos($value, '/') === 0) {
                    return true;
                } else {
                    $validator = Validator::make([$attribute => $value], [
                        $attribute => 'image:' . implode(",", $parameters)
                    ]);
                    return !$validator->fails();
                }
            }
        );
        Validator::extend(
            'file_or_url',
            function ($attribute, $value, $parameters) {
                if (Str::isUrl($value) || strpos($value, '/') === 0) {
                    return true;
                } else {
                    $validator = Validator::make([$attribute => $value], [
                        $attribute => 'file:' . implode(",", $parameters)
                    ]);
                    return !$validator->fails();
                }
            }
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
     * 加载路由
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return void
     */
    protected function loadRoutes(): void
    {
        // HTTP captcha routing
        if (config('cms.captcha.disable')) {
            $router = $this->app['router'];
            $router->get('captcha/api/{config?}', '\SimpleCMS\Framework\Http\Controllers\CaptchaController@getCaptchaApi');
            $router->get('captcha/{config?}', '\SimpleCMS\Framework\Http\Controllers\CaptchaController@getCaptcha');
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
        if (!is_dir(base_path('routes/backend'))) {
            @mkdir(base_path('routes/backend'), 0755);
        }
        if (!is_dir(base_path('routes/frontend'))) {
            @mkdir(base_path('routes/frontend'), 0755);
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
            __DIR__ . '/../database/migrations' => database_path('migrations'),
            __DIR__ . '/../database/seeders' => database_path('seeders'),
        ], 'simplecms');
    }
}
