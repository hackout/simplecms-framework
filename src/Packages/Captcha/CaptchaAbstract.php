<?php

namespace SimpleCMS\Framework\Packages\Captcha;

use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Session\Store as Session;
use Illuminate\Contracts\Config\Repository;
use Intervention\Image\{Image,ImageManager};
use Illuminate\Hashing\BcryptHasher as Hasher;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;

abstract class CaptchaAbstract
{    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * @var Repository
     */
    protected $config;

    /**
     * @var ImageManager
     */
    protected $imageManager;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Hasher
     */
    protected $hasher;

    /**
     * @var Str
     */
    protected $str;

    /**
     * @var ImageInterface
     */
    protected $canvas;

    /**
     * @var Image|ImageInterface
     */
    protected $image;

    /**
     * @var array
     */
    protected $backgrounds = [];

    /**
     * @var array
     */
    protected $fonts = [];

    /**
     * @var array
     */
    protected $fontColors = [];

    /**
     * @var int
     */
    protected $length = 5;

    /**
     * @var int
     */
    protected $width = 120;

    /**
     * @var int
     */
    protected $height = 36;

    /**
     * @var int
     */
    protected $angle = 15;

    /**
     * @var int
     */
    protected $lines = 3;

    /**
     * @var string|array
     */
    protected $characters;

    /**
     * @var array
     */
    protected $text;

    /**
     * @var int
     */
    protected $contrast = 0;

    /**
     * @var int
     */
    protected $quality = 90;

    /**
     * @var int
     */
    protected $sharpen = 0;

    /**
     * @var int
     */
    protected $blur = 0;

    /**
     * @var bool
     */
    protected $bgImage = true;

    /**
     * @var string
     */
    protected $bgColor = '#ffffff';

    /**
     * @var bool
     */
    protected $invert = false;

    /**
     * @var bool
     */
    protected $sensitive = false;

    /**
     * @var bool
     */
    protected $math = false;

    /**
     * @var int
     */
    protected $textLeftPadding = 4;

    /**
     * @var string
     */
    protected $fontsDirectory;

    /**
     * @var int
     */
    protected $expire = 60;

    /**
     * @var bool
     */
    protected $encrypt = true;

    /**
     * @var int
     */
    protected $marginTop = 0;

    /**
     * @var CaptchaUtils
     */
    protected $utils;

    public function __construct($files, $config, $session, $hasher, $str)
    {
        $this->files = $files;
        $this->config = $config;
        $this->session = $session;
        $this->hasher = $hasher;
        $this->str = $str;
        $driver = \extension_loaded('imagick') ? new ImagickDriver() : new GdDriver();
        $this->imageManager = new ImageManager($driver);
        $this->utils = new CaptchaUtils();
    }

    abstract protected function configure($config);
}