<?php
namespace SimpleCMS\Framework\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

/**
 * 数据导出快捷方式
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 */
trait ExportTemplateTrait
{

    /**
     * 导出数据
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param ?array $data
     * @return string
     */
    public function export(array $data = []): string
    {
        if (method_exists($this, 'getList') && $data) {
            $resultData = $this->getList($data)['items'];
        } else {
            $resultData = parent::getAll();
        }
        $path = Storage::path('public/exports/');
        if (!is_dir($path)) {
            @mkdir($path);
        }
        $fileName = 'exports/' . Str::uuid() . '.xlsx';
        $exportName = $this->getExportClassName();
        Excel::store(new $exportName($resultData), '/public/' . $fileName);
        return Storage::url($fileName);
    }

    /**
     * 获取导出类名称
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return string
     */
    protected function getExportClassName(): string
    {
        return property_exists($this, 'export') && $this->export ? $this->export : '\App\Exports\\' . basename($this->className) . 'Export';
    }

}