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
     * @author Dennis Lui <hackout@vip.qq.com>
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
            if ($drawing instanceof MemoryDrawing) {
                list($imageContents, $extension) = ObMemory::convert($drawing);
            } elseif ($drawing->getIsURL()) {
                list($imageContents, $extension) = Url::convert($drawing);
            } else {
                list($imageContents, $extension) = Zip::convert($drawing);
            }
            if (!$imageContents || !$extension) {
                continue;
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