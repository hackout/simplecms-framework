<?php

return [
    //FFMPEG 完整路径 D:\\ffmpeg-master-latest-win64-gpl\\bin\\ffmpeg.exe
    'ffmpeg_path' => (string) env('FFMPEG_PATH','D:\\ffmpeg-master-latest-win64-gpl\\bin\\ffmpeg.exe'),
    //FFPROBE 完整路径 D:\\ffmpeg-master-latest-win64-gpl\\bin\\ffprobe.exe
    'ffprobe_path' => (string) env('FFPROBE_PATH','D:\\ffmpeg-master-latest-win64-gpl\\bin\\ffprobe.exe'),
    //默认密码
    'default_password' => (string) env('DEFAULT_PASSWORD','123456'),
    'captcha' => [
        'disable' => env('CAPTCHA_DISABLE', false),
        'characters' => ['2', '3', '4', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'j', 'm', 'n', 'p', 'q', 'r', 't', 'u', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'M', 'N', 'P', 'Q', 'R', 'T', 'U', 'X', 'Y', 'Z'],
        'default' => [
            'length' => 4,
            'width' => 120,
            'height' => 36,
            'quality' => 90,
            'math' => false,
            'expire' => 60,
            'fontColors' => ['#2020f0', '#ff0000', '#ff00ff', '#00ffff', '#0f0f0f', '#000fff', '#fff000', '#0000ff'],
            'encrypt' => false,
        ],
        'math' => [
            'length' => 9,
            'width' => 120,
            'height' => 36,
            'quality' => 90,
            'math' => true,
        ],

        'flat' => [
            'length' => 6,
            'width' => 160,
            'height' => 46,
            'quality' => 90,
            'lines' => 6,
            'bgImage' => false,
            'bgColor' => '#ecf2f4',
            'fontColors' => ['#2c3e50', '#c0392b', '#16a085', '#c0392b', '#8e44ad', '#303f9f', '#f57c00', '#795548'],
            'contrast' => -5,
        ],
        'mini' => [
            'length' => 3,
            'width' => 60,
            'height' => 32,
        ],
        'inverse' => [
            'length' => 5,
            'width' => 120,
            'height' => 36,
            'quality' => 90,
            'sensitive' => true,
            'angle' => 12,
            'sharpen' => 10,
            'blur' => 2,
            'invert' => true,
            'contrast' => -5,
        ]
        ],
        'sms' => [
            'alibaba' => [
                'access_key_id' => env('ALIBABA_ACCESS_KEY_ID'),
                'access_key_secret' => env('ALIBABA_ACCESS_KEY_SECRET'),
                //签名
                'sign' => env('ALIBABA_SMS_SIGN','SimpleCMS'),
                //验证码模板编号
                'template' => env('ALIBABA_SMS_TEMPLATE'),
                //验证码变量
                'template_code' => env('ALIBABA_SMS_TEMPLATE_CODE','code')
            ]
        ]
];