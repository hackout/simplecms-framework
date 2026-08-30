<?php

namespace SimpleCMS\Framework\Packages\ExcelPlus;

use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing as BaseDrawing;

class Url
{
    /**
     * url of convert
     * @param BaseDrawing $drawing
     * @return array
     */
    public static function convert(BaseDrawing $drawing): array
    {
        $imageContents = file_get_contents($drawing->getPath());
        $filePath = tempnam(sys_get_temp_dir(), 'Drawing');
        file_put_contents($filePath, $imageContents);
        $mimeType = mime_content_type($filePath);
        $extension = File::mime2ext($mimeType);
        unlink($filePath);
        return [$imageContents, $extension];
    }
}