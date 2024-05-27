# Simple Framework

一个基于Laravel的扩展框架
测试包~请勿使用

## 环境配置要求

1. PHP 8.1+
2. Imagick
3. FFmpeg

## 安装指令

```bash
php artisan vendor:publish --provider="SimpleCMS\Framework\SimpleServiceProvider" --tag=cms
```

## 命令行

```bash
php artisan create:model # 创建模型并同时创建其他的控制器、服务等, 仅创建模型请用php artisan make:model
php artisan create:service SimpleService --model=Simple --type=backend #创建服务类
php artisan create:controller SimpleController --model=Simpler --type=backend #创建控制器
php artisan create:seeder #同php artisan db:seeder
php artisan create:migration #同php artisan make:migration
```

## 其他说明

服务及控制器等使用请参考IDE提示。
更多文档请自行查阅代码

## 借鉴

验证码借鉴： mews/captcha
