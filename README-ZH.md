# SimpleCMS Framework

一个用于 Laravel 应用的扩展框架，提供通用的服务层、媒体管理、菜单权限、系统配置、验证码、字典、权限脚手架和后台管理常用工具能力。

## 概览

这个框架适合放在 Laravel 应用之上，作为一个轻量且实用的后端基础层，主要覆盖以下能力：

- 服务化 CRUD 逻辑
- 模型与媒体辅助能力
- 菜单与角色权限管理
- 系统配置读取
- 验证码与校验工具
- 字典管理
- Excel 转换与导出辅助
- 请求日志集成
- 后台/前台控制器脚手架

## 这个包解决什么问题

这个包适合在 Laravel 项目中统一一套常用后端基础能力，而不需要在每个项目里重复实现：

- 统一的服务层封装，减少重复 CRUD 代码
- 通用媒体和文件辅助能力
- 可复用的菜单、系统配置和字典工具
- 常见的后台表单校验与验证码支持
- 更快的脚手架生成与项目初始化

## 环境要求

- PHP 8.2+
- Laravel 12+
- FFmpeg（用于视频/媒体相关功能）
- 建议安装 Imagick 以支持更稳定的图片处理

## 安装

使用 Composer 安装：

```bash
composer require simplecms/framework
```

发布配置和资源：

```bash
php artisan vendor:publish --provider="SimpleCMS\\Framework\\SimpleServiceProvider" --tag=simplecms
```

初始化包结构和迁移资源：

```bash
php artisan simplecms:init
php artisan migrate
```

## 快速开始

### 1. 使用基础服务层

```php
use App\Models\User;
use SimpleCMS\Framework\Services\SimpleService;

$service = new SimpleService();
$service->setModel(User::class);
$service->setQuery([['status', '=', 1]]);

$users = $service->getAll();
```

### 2. 给模型增加媒体支持

```php
use Illuminate\Database\Eloquent\Model;
use SimpleCMS\Framework\Contracts\SimpleMedia;
use SimpleCMS\Framework\Traits\MediaAttributeTrait;

class Article extends Model implements SimpleMedia
{
    use MediaAttributeTrait;

    const MEDIA_FILE = 'file';

    protected $hasOneMedia = ['file'];
}
```

### 3. 读取系统配置和菜单数据

```php
use SystemConfig;
use Menu;

$value = SystemConfig::getConfigValue('site_name');
$menuTree = Menu::backendMenu();
```

## 项目结构一览

```text
src/
├── Console/
├── Contracts/
├── Database/
├── Enums/
├── Exceptions/
├── Facades/
├── Http/
├── Models/
├── Observers/
├── Packages/
├── Services/
├── Traits/
├── Validation/
├── helpers/
├── HasRole.php
├── SimpleServiceProvider.php
└── ...
```

## 功能特性

### 服务层

框架提供了基础服务抽象，用于统一模型查询、筛选和 CRUD 逻辑，便于在项目里复用并按业务继续扩展。

### 媒体支持

模型可以通过 media trait 和媒体契约接入通用媒体能力，适合做文章、附件、封面、缩略图等一对一/集合化媒体场景。

### Facades

包内注册了一组常用 facade：

```php
use Captcha;
use Dict;
use ExcelConvert;
use ExcelDrawing;
use Menu;
use SystemConfig;
use SystemInfo;
use Finger;
```

常见使用示例：

```php
$value = SystemConfig::getConfigValue('site_name');
$menuTree = Menu::backendMenu();
$dictItems = Dict::getOptionsByCode('status');
```

### 验证器

包内已注册常用自定义验证规则，包括：

- id_card
- mobile
- telephone
- phone
- chinese
- car_number
- company_id
- chinese_postcode
- captcha
- captcha_api
- image_or_url
- file_or_url

### 辅助函数

会自动加载一批 helper 方法，例如：

- 验证码函数
- JSON 返回函数
- URL / 手机 / Base64 / 图片判断函数
- 下载函数
- 路由加载与动态 require 工具

示例：

```php
$json = json_success(['ok' => true]);
```

### Middleware

包中提供权限与语言中间件的注册入口：

```php
use SimpleCMS\Framework\Http\Middleware\CheckPermission;

->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => CheckPermission::class,
    ]);
})
```

### 命令行脚手架

包提供的常用生成命令：

```bash
php artisan create:model
php artisan create:service SimpleService --model=Simple --type=backend
php artisan create:controller SimpleController --model=Simple --type=backend
php artisan create:route Simple --type=backend
php artisan create:seeder
php artisan create:migration
php artisan simplecms:init
```

## 配置文件

发布后的配置位置：

```bash
config/cms.php
```

配置项包括：

- ffmpeg_path / ffprobe_path
- default_password
- captcha 配置
- SMS 配置

示例：

```php
return [
    'captcha' => [
        'disable' => false,
        'default' => [
            'length' => 4,
            'width' => 120,
            'height' => 36,
        ],
    ],
];
```

## 说明

- 该包适合作为 Laravel 应用的扩展层，而不是独立应用本体。
- 它依赖 Laravel 的 Service Provider、配置、路由和中间件机制。
- 部分资源、迁移和目录会通过 provider 发布到主应用项目中，可按项目需要继续调整。

## 许可证

MIT
