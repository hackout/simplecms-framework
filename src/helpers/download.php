<?php

use Symfony\Component\HttpFoundation\BinaryFileResponse;


/**
 * 下载文件或者图片
 *
 * @param string $file 文件路径、文件内容
 * @param string|null $name 下载保存文件名
 * @param array $headers 输出头信息
 * @return BinaryFileResponse
 */
function download(string $file, string $name = null, array $headers = []): BinaryFileResponse
{
    return response()->download($file, $name, $headers);
}