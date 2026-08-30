<?php
namespace SimpleCMS\Framework\Packages\ExcelPlus;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use SimpleCMS\Framework\Exceptions\SimpleException;
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
     * @return array
     */
    public function toArray(): array
    {
        $objRead = IOFactory::createReader('Xlsx');
        $obj = $objRead->load($this->uploadedFile);
        $currSheet = $obj->getActiveSheet();
        $array = [];
        $this->checkPath();

        foreach ($currSheet->getDrawingCollection() as $drawing) {
            list($imageContents, $extension) = $this->convertDrawing($drawing);

            if (!$imageContents || !$extension) {
                continue;
            }

            $fileName = $this->saveImage($imageContents, $extension);
            $coordinates = Coordinate::indexesFromString($drawing->getCoordinates());
            $rowKey = $coordinates['1'] - 1;
            $cellKey = $coordinates['0'] - 1;

            $array[$rowKey][$cellKey][] = $fileName;
        }

        return $array;
    }

    /**
     * Convert drawing to image contents and extension.
     *
     * @param $drawing
     * @return array
     */
    protected function convertDrawing($drawing): array
    {
        if ($drawing instanceof MemoryDrawing) {
            return ObMemory::convert($drawing);
        } elseif ($drawing->getIsURL()) {
            return Url::convert($drawing);
        } else {
            return Zip::convert($drawing);
        }
    }

    /**
     * Save image to storage and return the file name.
     *
     * @param string $imageContents
     * @param string $extension
     * @return string
     */
    protected function saveImage(string $imageContents, string $extension): string
    {
        $fileName = Storage::path('public/imports/' . Str::Uuid() . '.' . $extension);
        file_put_contents($fileName, $imageContents);
        return $fileName;
    }


    /**
     * Check the path
     * @throws \SimpleCMS\Framework\Exceptions\SimpleException
     * @return void
     */
    protected function checkPath(): void
    {
        $path = Storage::path('public/imports/');
        if (!is_dir($path)) {
            try {
                mkdir($path);
            } catch (\Throwable $th) {
                throw new SimpleException("No operation permission for this directory");
            }
        }
    }
}