<?php

namespace langdonglei\util\library;

class File
{
    public static function zip($zip)
    {
        $exclude = ['.git', '.DS_Store', 'Thumbs.db'];
        if (is_file($zip)) {
            unlink($zip);
        }
        $zipArchive = new \ZipArchive;
        $zipArchive->open($zip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(__DIR__), \RecursiveIteratorIterator::LEAVES_ONLY) as $file) {
            if (!$file->isDir()) {
                $filePath     = $file->getRealPath();
                $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', substr($filePath, strlen(__DIR__)));
                if (!in_array($file->getFilename(), $exclude)) {
                    $zipArchive->addFile($filePath, $relativePath);
                }
            }
        }
        $zipArchive->close();
    }

    public static function unzip($zip)
    {
        $zipFile = new ZipFile();
        $zipFile->openFile($zip);
        $zipFile->extractTo(__DIR__);
        $zipFile->close();
    }

}