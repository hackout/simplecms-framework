<?php
namespace SimpleCMS\Framework\Packages\ExcelPlus;

use File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Excel 提取图片
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 */
class Drawing
{
    /**
     * 初始化并加载上传图像
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  UploadedFile|string $uploadedFile Excel上传包或本地文件
     */
    public function __construct(private UploadedFile|string $uploadedFile)
    {
    }

    /**
     * 提取图片到Collection
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return array
     */
    public function toArray(): array
    {
        $objRead = IOFactory::createReader('Xlsx');
        $obj = $objRead->load($this->uploadedFile);
        $currSheet = $obj->getActiveSheet();
        $array = [];
        foreach ($currSheet->getDrawingCollection() as $drawing) {
            if ($drawing instanceof MemoryDrawing) {
                ob_start();
                call_user_func(
                    $drawing->getRenderingFunction(),
                    $drawing->getImageResource()
                );
                $imageContents = ob_get_contents();
                ob_end_clean();
                switch ($drawing->getMimeType()) {
                    case MemoryDrawing::MIMETYPE_PNG:
                        $extension = 'png';
                        break;
                    case MemoryDrawing::MIMETYPE_GIF:
                        $extension = 'gif';
                        break;
                    case MemoryDrawing::MIMETYPE_JPEG:
                        $extension = 'jpg';
                        break;
                }
            } else {
                if ($drawing->getPath()) {
                    if ($drawing->getIsURL()) {
                        $imageContents = file_get_contents($drawing->getPath());
                        $filePath = tempnam(sys_get_temp_dir(), 'Drawing');
                        file_put_contents($filePath, $imageContents);
                        $mimeType = mime_content_type($filePath);
                        $extension = File::mime2ext($mimeType);
                        unlink($filePath);
                    } else {
                        $zipReader = fopen($drawing->getPath(), 'r');
                        $imageContents = '';
                        while (!feof($zipReader)) {
                            $imageContents .= fread($zipReader, 1024);
                        }
                        fclose($zipReader);
                        $extension = $drawing->getExtension();
                    }
                }
            }
            $path = Storage::path('public/imports/');
            if (!is_dir($path)) {
                @mkdir($path);
            }
            $fileName = Storage::path('public/imports/' . Str::Uuid() . '.' . $extension);
            file_put_contents($fileName, $imageContents);
            $coordinates = Coordinate::indexesFromString($drawing->getCoordinates());
            $rowKey = $coordinates['1'] - 1;
            $cellKey = $coordinates['0'] - 1;
            if (!array_key_exists($rowKey, $array)) {
                $array[$rowKey] = [];
            }
            if (!array_key_exists($cellKey, $array[$rowKey])) {
                $array[$rowKey][$cellKey] = [];
            }
            $array[$rowKey][$cellKey][] = $fileName;
        }

        return $array;
    }

}