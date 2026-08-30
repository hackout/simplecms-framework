# SimpleCMS Framework

A Laravel package for building admin and content-driven applications with reusable service logic, media helpers, menu management, configuration access, captcha utilities, dictionary handling, and permission-oriented scaffolding.

## Overview

This framework is designed to sit on top of Laravel and provide a lightweight, practical foundation for backend applications. It focuses on common patterns used in content management and admin systems:

- service-based CRUD patterns
- reusable model and media helpers
- menu and role utilities
- system configuration access
- captcha and validation helpers
- dictionary management
- Excel conversion helpers
- request log integration
- backend/frontend controller scaffolding

## Why this package

This package is useful when you want a consistent foundation across Laravel projects without forcing a rigid application structure. It gives you:

- a standard service abstraction for CRUD and model access
- common media and file-related helpers
- ready-made menu, system config, and dict utilities
- validation and captcha building blocks for admin workflows
- scaffolding commands for faster project setup

## Requirements

- PHP 8.2+
- Laravel 12+
- FFmpeg for media/video-related features
- Imagick recommended for more stable image processing

## Installation

Install the package with Composer:

```bash
composer require simplecms/framework
```

Publish the package config and resources:

```bash
php artisan vendor:publish --provider="SimpleCMS\\Framework\\SimpleServiceProvider" --tag=simplecms
```

Initialize the package structure and migration resources:

```bash
php artisan simplecms:init
php artisan migrate
```

## Quick Start

### 1. Use the base service layer

```php
use App\Models\User;
use SimpleCMS\Framework\Services\SimpleService;

$service = new SimpleService();
$service->setModel(User::class);
$service->setQuery([['status', '=', 1]]);

$users = $service->getAll();
```

### 2. Add media support to a model

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

### 3. Read system config and menu data

```php
use SystemConfig;
use Menu;

$value = SystemConfig::getConfigValue('site_name');
$menuTree = Menu::backendMenu();
```

## Project structure at a glance

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

## Package Features

### Service layer

The framework includes a base service abstraction for model-based operations and query building. It is intended to reduce repetitive CRUD logic while keeping the result easy to customize per application.

### Media support

Models can register media collections and one-to-one media fields by implementing the media contract together with the media trait. This integrates naturally with Laravel model patterns and the Spatie MediaLibrary stack.

### Facades

The package registers several facades for quick access to common utilities:

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

Typical usage:

```php
$value = SystemConfig::getConfigValue('site_name');
$menuTree = Menu::backendMenu();
$dictItems = Dict::getOptionsByCode('status');
```

### Validators

The package registers custom validation rules for common business requirements:

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

### Helpers

Helper functions are auto-loaded by the package. These include:

- captcha utilities
- JSON response helpers
- URL/mobile/base64 validation helpers
- download helpers
- route loading and dynamic require utilities

Example:

```php
$json = json_success(['ok' => true]);
```

### Middleware

The package exposes middleware registration hooks for permission and locale handling.

```php
use SimpleCMS\Framework\Http\Middleware\CheckPermission;

->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => CheckPermission::class,
    ]);
})
```

### Console commands

The package includes scaffolding commands to speed up project setup:

```bash
php artisan create:model
php artisan create:service SimpleService --model=Simple --type=backend
php artisan create:controller SimpleController --model=Simple --type=backend
php artisan create:route Simple --type=backend
php artisan create:seeder
php artisan create:migration
php artisan simplecms:init
```

## Configuration

The published config file lives at:

```bash
config/cms.php
```

It includes settings such as:

- ffmpeg_path / ffprobe_path
- default_password
- captcha config
- SMS config

Example:

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

## Notes

- This package is intended to enhance a Laravel application rather than be a standalone app.
- It follows Laravel service provider, config, route, and middleware conventions.
- Some assets and migrations are published into the host application and may be customized per project.

## License

MIT
