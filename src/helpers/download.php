<?php

use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Download a file from the filesystem.
 *
 * @param string|\SplFileInfo $file
 * @param string|null $name
 * @param array $headers
 * @param string $disposition
 * @return BinaryFileResponse
 */
function download(string|\SplFileInfo $file, ?string $name = null, array $headers = [], string $disposition = 'attachment'): BinaryFileResponse
{
    $filePath = $file instanceof \SplFileInfo ? $file->getPathname() : $file;

    if ($filePath === '') {
        throw new \InvalidArgumentException('The download file path cannot be empty.');
    }

    return response()->download($filePath, $name, $headers, $disposition);
}