<?php
namespace SimpleCMS\Framework\Services\Traits;

use SimpleCMS\Framework\Services\Work\AddMedia;
use SimpleCMS\Framework\Services\Work\HasMedia;
use SimpleCMS\Framework\Services\SimpleUploadService;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * 附件处理类
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 * 
 * @use \SimpleCMS\Framework\Services\BaseService
 * @use \SimpleCMS\Framework\Services\SimpleService
 */
trait MediaServiceTrait
{

    /**
     * 上传文件/图片/视频
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return SimpleUploadService
     */
    public function upload(): SimpleUploadService
    {
        return new SimpleUploadService;
    }

    /**
     * 更新附件
     *
     * @param array $files
     * @param array $multipleFiles
     * @param array $mediaFields
     * @return void
     */
    protected function updateMedia(array $files, array $multipleFiles, array $mediaFields): void
    {
        if (!empty($files)) {
            $this->processSingleFiles($files, $mediaFields);
        }
        if (!empty($multipleFiles)) {
            $this->processMultipleFiles($multipleFiles, $mediaFields);
        }
    }

    /**
     * 处理单个文件
     *
     * @param array $files
     * @param array $mediaFields
     * @return void
     */
    private function processSingleFiles(array $files, array $mediaFields): void
    {
        if (empty($mediaFields)) {
            $mediaColumn = $this->getMediaColumn() ?? head(array_keys($files));
            $this->addMedia(head($files), $mediaColumn);
        } else {
            $this->processFilesWithFields($files, $mediaFields, false);
        }
    }

    /**
     * 处理多个文件
     *
     * @param array $multipleFiles
     * @param array $mediaFields
     * @return void
     */
    private function processMultipleFiles(array $multipleFiles, array $mediaFields): void
    {
        if (empty($mediaFields)) {
            $mediaColumn = $this->getMediaColumn() ?? head(array_keys($multipleFiles));
            $this->addMultipleMedia(head($multipleFiles), $mediaColumn);
        } else {
            $this->processFilesWithFields($multipleFiles, $mediaFields, true);
        }
    }

    /**
     * 处理带有字段的文件
     *
     * @param array $files
     * @param array $mediaFields
     * @param bool $isMultiple
     * @return void
     */
    private function processFilesWithFields(array $files, array $mediaFields, bool $isMultiple): void
    {
        foreach ($files as $field => $file) {
            if (array_key_exists($field, $mediaFields) && $mediaFields[$field]) {
                if ($isMultiple) {
                    $this->addMultipleMedia($file, $mediaFields[$field]);
                } else {
                    $this->addMedia($file, $mediaFields[$field]);
                }
            }
        }
    }

    /**
     * 添加附件
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  UploadedFile|string $file
     * @param  string              $columnName
     * @return void
     */
    public function addMedia(UploadedFile|string $file, string $columnName): void
    {
        AddMedia::run($this->/** @scrutinizer ignore-call */ getItem(), $file, $columnName);
    }

    /**
     * 添加附件组
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @param  array<UploadedFile|string> $files
     * @param  string              $columnName
     * @return void
     */
    public function addMultipleMedia(array $files, string $columnName): void
    {
        foreach ($files as $file) {
            $this->addMedia($file, $columnName);
        }
    }

    /**
     * 检查是否存在Media关系
     *
     * @author Dennis Lui <hackout@vip.qq.com>
     * @return boolean
     */
    protected function hasMedia(): bool
    {
        return HasMedia::run($this->/** @scrutinizer ignore-call */ getModel());
    }

    /**
     * 获取Media Key
     */
    protected function getMediaColumn(): ?string
    {
        $modelClass = $this->/** @scrutinizer ignore-call */ getClassName();
        return defined($modelClass . '::MEDIA_FILE') ? $modelClass::MEDIA_FILE : null;
    }


}