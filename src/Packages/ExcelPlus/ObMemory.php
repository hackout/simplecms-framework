<?php

namespace SimpleCMS\Framework\Packages\ExcelPlus;

use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;

class ObMemory
{
    /**
     * MemoryDrawing of convert
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing $drawing
     * @return array
     */
    public static function convert(MemoryDrawing $drawing): array
    {
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
            default:
                $extension = null;
                break;
        }
        return [$imageContents, $extension];
    }
}