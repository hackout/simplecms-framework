<?php

namespace SimpleCMS\Framework\Packages\Captcha;

use function is_string;
use function str_split;
use Illuminate\Support\Str;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Session\Store as Session;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Hashing\BcryptHasher as Hasher;
use Intervention\Image\Geometry\Factories\LineFactory;

/**
 * 验证码类
 * 
 * @author Dennis Lui <hackout@vip.qq.com>
 */
class Captcha extends CaptchaAbstract
{
    use CaptchaText, CaptchaImage, CaptchaConfig;

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
        parent::__construct($files, $config, $session, $hasher, $str);
        $this->characters = config('cms.captcha.characters', ['1', '2', '3', '4', '6', '7', '8', '9']);
        $this->fontsDirectory = config('cms.captcha.fontsDirectory', __DIR__ . '/assets/fonts');
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
        $this->fonts = array_map(function ($file) {
            return $file->getPathName();
        }, $this->fonts);

        $this->fonts = array_values($this->fonts); //reset fonts array index

        $this->configure($config);

        $generator = $this->generate();
        $this->text = $generator['value'];
        $this->canvas = $this->imageManager->read($this->background());
        $this->canvas->resize($this->width, $this->height);
        $this->addImage();

        Cache::put($this->utils->get_cache_key($generator['key']), $generator['value'], $this->expire);

        return $api ? [
            'sensitive' => $generator['sensitive'],
            'key' => $generator['key'],
            'img' => $this->image->toJpeg($this->quality)->toDataUri()
        ] : response($this->image->toJpeg($this->quality), '200', ['Content-Type' => 'image/jpeg']);
    }

    private function addImage(): void
    {
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
}
