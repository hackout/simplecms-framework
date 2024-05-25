<?php
namespace SimpleCMS\Framework\Services;

use FFMpeg\FFMpeg;
use Illuminate\Support\Str;
use FFMpeg\Coordinate\TimeCode;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Imagick\Driver;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * 临时文件上传管理
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 */
class SimpleUploadService
{
    /**
     * 创建缩率图
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  string      $file
     * @param  integer     $width
     * @return string|null
     */
    public function makeThumbnail(string $file, int $width = 0): string|null
    {
        if ($width && Storage::has($file)) {
            $manager = new ImageManager(new Driver());
            $image = $manager->read(Storage::path($file));
            $image->scale(width: $width);
            $path = str_replace('.' . Str::of(Storage::path($file))->afterLast('.'), '_100.png', Storage::path($file));
            $image->toPng()->save($path);
            return $path;
        }
        return null;
    }

    /**
     * 创建视频缩率图
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  string $file
     * @return string
     */
    public function makeScreenshot(string $file): string
    {
        $ffmpegConfig = [
            'ffmpeg.binaries' => config('cms.ffmpeg_path'),
            'ffprobe.binaries' => config('cms.ffprobe_path')
        ];
        $ffmpeg = FFMpeg::create($ffmpegConfig);
        $video = $ffmpeg->open(Storage::path($file));
        $poster = str_replace(Str::of(Storage::path($file))->afterLast('.'), 'jpg', Storage::path($file));
        $frame = $video->frame(TimeCode::fromSeconds(1));
        $frame->save($poster, true);
        return $poster;
    }

    /**
     * 上传文件
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  UploadedFile $fileBag
     * @param  string       $dir
     * @return array
     */
    public function upload(UploadedFile $fileBag, $dir = 'images', int $width = 100): array
    {
        $uuid = Str::uuid();
        $filename = $uuid . '.' . $fileBag->getClientOriginalExtension();
        $file = Storage::putFileAs('public/' . $dir . '/temp', $fileBag, $filename);
        $result = [
            'url' => Storage::url($file),
            'alt' => $fileBag->getClientOriginalName(),
            'uuid' => $uuid,
            'name' => $filename,
            'path' => 'public/' . $dir . '/temp/' . $filename,
            'poster' => null
        ];
        if ($width) {
            $result['poster'] = $this->makeThumbnail($file, $width);
        }
        return $result;
    }

    /**
     * 上传图片
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  UploadedFile $fileBag
     * @return array
     */
    public function image(UploadedFile $fileBag): array
    {
        return $this->upload($fileBag);
    }

    /**
     * 上传文件
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  UploadedFile $fileBag
     * @return array
     */
    public function file(UploadedFile $fileBag): array
    {
        return $this->upload($fileBag, 'files', 0);
    }

    /**
     * 上传视频
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  UploadedFile $fileBag
     * @return array
     */
    public function video(UploadedFile $fileBag): array
    {
        $uploaded = $this->upload($fileBag, 'videos', 0);
        $uploaded['poster'] = $this->makeScreenshot($uploaded['path']);
        return $uploaded;
    }
}