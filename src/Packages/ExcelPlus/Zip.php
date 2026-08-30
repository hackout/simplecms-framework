<?php

namespace SimpleCMS\Framework\Packages\ExcelPlus;

use PhpOffice\PhpSpreadsheet\Worksheet\Drawing as BaseDrawing;

class Zip
{
    /**
     * zip of convert
     * @param BaseDrawing $drawing
     * @return array
     */
    public static function convert(BaseDrawing $drawing): array
    {
        $zipReader = fopen($drawing->getPath(), 'r');
        $imageContents = '';
        while (!feof($zipReader)) {
            $imageContents .= fread($zipReader, 1024);
        }
        fclose($zipReader);
        $extension = $drawing->getExtension();
        return [$imageContents, $extension];
    }
}