<?php
namespace SimpleCMS\Framework\Traits;

use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

/**
 * 数据导出快捷方式
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 * 
 * @use \SimpleCMS\Framework\Services\SimpleService
 * @abstract \SimpleCMS\Framework\Services\SimpleService
 * 
 * @const string EXPORT_NAME 
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
        if (method_exists($this, 'getList') && !empty($data)) {
            $resultData = $this->getList($data)['items'];
        } else {
            $resultData = parent::getAll();
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
        return defined('static::EXPORT_NAME') ? static::EXPORT_NAME : '\App\Exports\\' . basename($this->className) . 'Export';
    }

}