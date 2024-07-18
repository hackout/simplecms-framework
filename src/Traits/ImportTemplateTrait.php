<?php
namespace SimpleCMS\Framework\Traits;

use Maatwebsite\Excel\Facades\Excel;
use SimpleCMS\Framework\Exceptions\SimpleException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\BinaryFileResponse;


/**
 * 增加导出导入及模板
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 * 
 * @use \SimpleCMS\Framework\Services\SimpleService
 * @abstract \SimpleCMS\Framework\Services\SimpleService
 * 
 * @const string IMPORT_NAME 
 */
trait ImportTemplateTrait
{
    /**
     * 获取导入模板路径
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return string
     */
    public function getImportTemplate(): string
    {
        return resource_path('/imports/' . $this->getImportBaseName() . '.xlsx');
    }

    /**
     * 获取模板文件名
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return string
     */
    protected function getImportBaseName(): string
    {
        return property_exists($this, 'template') && $this->template ? $this->template : basename($this->className);
    }

    /**
     * 下载导入模板
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return BinaryFileResponse
     */
    public function downloadImportTemplate(): BinaryFileResponse
    {
        if (!file_exists($this->getImportTemplate())) {
            throw new SimpleException('模板不存在');
        }
        return response()->download($this->getImportTemplate(), $this->getImportBaseName() . 'ImportTemplate');
    }


    /**
     * 统一导入数据
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  UploadedFile $file
     * @return void
     */
    public function import(UploadedFile $file)
    {
        $import = $this->getImportClassName();
        Excel::import(new $import, $file);
    }

    /**
     * 获取导入类名称
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return string
     */
    protected function getImportClassName(): string
    {
        // 使用类常量
        if (defined('self::IMPORT_NAME')) {
            return self::IMPORT_NAME;
        }

        return '\App\Exports\\' . basename($this->className) . 'Import';
    }
}