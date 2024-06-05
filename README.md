# Simple Framework

一个基于Laravel的扩展框架

测试包~请勿使用

## 环境配置要求

1. PHP 8.1+
2. Imagick 7+
3. FFmpeg

## 安装指令

```bash
php artisan vendor:publish --provider="SimpleCMS\Framework\SimpleServiceProvider" --tag=cms
php artisan vendor:publish --provider="SimpleCMS\Framework\SimpleServiceProvider" --tag=config
```

## 命令行

```bash
php artisan create:model # 创建模型并同时创建其他的控制器、服务等, 仅创建模型请用php artisan make:model
php artisan create:service SimpleService --model=Simple --type=backend #创建服务类
php artisan create:controller SimpleController --model=Simpler --type=backend #创建控制器
php artisan create:route Simple --type=backend #创建路由
php artisan create:seeder #同php artisan db:seeder
php artisan create:migration #同php artisan make:migration
```

## 辅助函数

Continue...

## Facades

```bash
use Captcha; #验证码 
use Dict; #字典 
use ExcelConvert; #Excel转换 
use ExcelDrawing; #Excel提取图片 
use Menu; #菜单 
use SystemConfig; #系统设置 
use SystemInfo; #系统环境参数
```

## 其他说明

服务及控制器等使用请参考IDE提示。
更多文档请自行查阅代码

## 借鉴

验证码借鉴： mews/captcha
