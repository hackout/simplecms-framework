<?php

namespace SimpleCMS\Framework\Packages\Captcha;

use function str_split;
use function is_string;
use Illuminate\Support\Str;
use Intervention\Image\Image;
use Illuminate\Support\HtmlString;
use Intervention\Image\ImageManager;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Session\Store as Session;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Hashing\BcryptHasher as Hasher;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Geometry\Factories\LineFactory;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;

/**
 * 验证码类
 * 
 * @author Dennis Lui <hackout@vip.qq.com>
 */
class Captcha
{
    /**
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
     * @var array|string
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
     * Constructor
     *
     * @param Filesystem $files
     * @param Repository $config
     * @param ImageManager $imageManager
     * @param Session $session
     * @param Hasher $hasher
     * @param Str $str
     * @throws \Exception
     * @internal param Validator $validator
     */
    public function __construct(
        Filesystem $files,
        Repository $config,
        Session $session,
        Hasher $hasher,
        Str $str
    ) {
        $this->files = $files;
        $this->config = $config;
        $driver = \extension_loaded('imagick') ? new ImagickDriver() : new GdDriver();
        $this->imageManager = new ImageManager($driver);
        $this->session = $session;
        $this->hasher = $hasher;
        $this->str = $str;
        $this->characters = config('cms.captcha.characters', ['1', '2', '3', '4', '6', '7', '8', '9']);
        $this->fontsDirectory = config('cms.captcha.fontsDirectory', __DIR__ . '/assets/fonts');
    }

    /**
     * @param string $config
     * @return void
     */
    protected function configure($config)
    {
        if ($this->config->has('cms.captcha.' . $config)) {
            foreach ($this->config->get('cms.captcha.' . $config) as $key => $val) {
                $this->{$key} = $val;
            }
        }
    }

    /**
     * Create captcha image
     *
     * @param string $config
     * @param bool $api
     * @return array|mixed
     * @throws \Exception
     */
    public function create(string $config = 'default', bool $api = false)
    {
        $this->backgrounds = $this->files->files(__DIR__ . '/assets/backgrounds');
        $this->fonts = $this->files->files($this->fontsDirectory);

        if (version_compare(app()->version(), '5.5.0', '>=')) {
            $this->fonts = array_map(function ($file) {
                return $file->getPathName();
            }, $this->fonts);
        }

        $this->fonts = array_values($this->fonts); //reset fonts array index

        $this->configure($config);

        $generator = $this->generate();
        $this->text = $generator['value'];
        $this->canvas = $this->imageManager->read($this->background());
        $this->canvas->resize($this->width, $this->height);
        $this->image = $this->canvas;

        if ($this->contrast != 0) {
            $this->image->contrast($this->contrast);
        }

        $this->text();

        $this->lines();

        if ($this->sharpen) {
            $this->image->sharpen($this->sharpen);
        }
        if ($this->invert) {
            $this->image->invert();
        }
        if ($this->blur) {
            $this->image->blur($this->blur);
        }

        Cache::put($this->get_cache_key($generator['key']), $generator['value'], $this->expire);

        return $api ? [
            'sensitive' => $generator['sensitive'],
            'key' => $generator['key'],
            'img' => $this->image->toJpeg($this->quality)->toDataUri()
        ] : response($this->image->toJpeg($this->quality), '200', ['Content-Type' => 'image/jpeg']);
    }

    /**
     * Image backgrounds
     *
     * @return string
     */
    protected function background(): string
    {
        return $this->backgrounds[rand(0, count($this->backgrounds) - 1)];
    }

    /**
     * Generate captcha text
     *
     * @return array
     */
    protected function generate(): array
    {
        $characters = is_string($this->characters) ? str_split($this->characters) : $this->characters;

        $bag = [];

        if ($this->math) {
            $x = random_int(10, 30);
            $y = random_int(1, 9);
            $bag = "$x + $y = ";
            $key = $x + $y;
            $key .= '';
        } else {
            for ($i = 0; $i < $this->length; $i++) {
                $char = $characters[rand(0, count($characters) - 1)];
                $bag[] = $this->sensitive ? $char : $this->str->lower($char);
            }
            $key = implode('', $bag);
        }

        $hash = $this->hasher->make($key);
        if ($this->encrypt)
            $hash = Crypt::encrypt($hash);

        $this->session->put('captcha', [
            'sensitive' => $this->sensitive,
            'key' => $hash,
            'encrypt' => $this->encrypt
        ]);

        return [
            'value' => $bag,
            'sensitive' => $this->sensitive,
            'key' => $hash
        ];
    }

    /**
     * Writing captcha text
     *
     * @return void
     */
    protected function text(): void
    {
        $marginTop = $this->image->height() / $this->length;
        if ($this->marginTop !== 0) {
            $marginTop = $this->marginTop;
        }

        $text = $this->text;
        if (is_string($text)) {
            $text = str_split($text);
        }

        foreach ($text as $key => $char) {
            $marginLeft = $this->textLeftPadding + ($key * ($this->image->width() - $this->textLeftPadding) / $this->length);

            $this->image->text($char, $marginLeft, $marginTop, function ($font) {
                /* @var Font $font */
                $font->file($this->font());
                $font->size($this->fontSize());
                $font->color($this->fontColor());
                $font->align('left');
                $font->valign('top');
                $font->angle($this->angle());
            });
        }
    }

    /**
     * Image fonts
     *
     * @return string
     */
    protected function font(): string
    {
        return $this->fonts[rand(0, count($this->fonts) - 1)];
    }

    /**
     * Random font size
     *
     * @return int
     */
    protected function fontSize(): int
    {
        return rand($this->image->height() - 10, $this->image->height());
    }

    /**
     * Random font color
     *
     * @return string
     */
    protected function fontColor(): string
    {
        if (!empty($this->fontColors)) {
            $color = $this->fontColors[rand(0, count($this->fontColors) - 1)];
        } else {
            $color = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        }

        return $color;
    }

    /**
     * Angle
     *
     * @return int
     */
    protected function angle(): int
    {
        return rand((-1 * $this->angle), $this->angle);
    }

    /**
     * Random image lines
     *
     * @return Image|ImageManager
     */
    protected function lines()
    {
        for ($i = 0; $i <= $this->lines; $i++) {
            $this->image->drawLine(function (LineFactory $line) use ($i) {
                $line->from(rand(0, $this->image->width()) + $i * rand(0, $this->image->height()), rand(0, $this->image->width()) + $i * rand(0, $this->image->height()));
                $line->to(rand(0, $this->image->width()), rand(0, $this->image->height()));
                $line->color($this->fontColor());
            });
        }

        return $this->image;
    }

    /**
     * Captcha check
     *
     * @param string $value
     * @return bool
     */
    public function check(string $value): bool
    {
        if (!$this->session->has('captcha')) {
            return false;
        }

        $key = $this->session->get('captcha.key');
        $sensitive = $this->session->get('captcha.sensitive');
        $encrypt = $this->session->get('captcha.encrypt');

        if (!Cache::pull($this->get_cache_key($key))) {
            $this->session->remove('captcha');
            return false;
        }

        if (!$sensitive) {
            $value = $this->str->lower($value);
        }

        if ($encrypt)
            $key = Crypt::decrypt($key);
        $check = $this->hasher->check($value, $key);
        // if verify pass,remove session
        if ($check) {
            $this->session->remove('captcha');
        }

        return $check;
    }

    /**
     * Returns the md5 short version of the key for cache
     *
     * @param string $key
     * @return string
     */
    protected function get_cache_key($key)
    {
        return 'captcha_' . md5($key);
    }

    /**
     * Captcha check
     *
     * @param string $value
     * @param string $key
     * @param string $config
     * @return bool
     */
    public function check_api($value, $key, $config = 'default'): bool
    {
        if (!Cache::pull($this->get_cache_key($key))) {
            return false;
        }

        $this->configure($config);

        if (!$this->sensitive)
            $value = $this->str->lower($value);
        if ($this->encrypt)
            $key = Crypt::decrypt($key);
        return $this->hasher->check($value, $key);
    }

    /**
     * Generate captcha image source
     *
     * @param string $config
     * @return string
     */
    public function src(string $config = 'default'): string
    {
        return url('captcha/' . $config) . '?' . $this->str->random(8);
    }

    /**
     * Generate captcha image html tag
     *
     * @param string $config
     * @param array $attrs
     * $attrs -> HTML attributes supplied to the image tag where key is the attribute and the value is the attribute value
     * @return string
     */
    public function img(string $config = 'default', array $attrs = []): string
    {
        $attrs_str = '';
        foreach ($attrs as $attr => $value) {
            if ($attr == 'src') {
                //Neglect src attribute
                continue;
            }

            $attrs_str .= $attr . '="' . $value . '" ';
        }
        return new HtmlString('<img src="' . $this->src($config) . '" ' . trim($attrs_str) . '>');
    }
}
