<?php

namespace langdonglei\util;

use Exception;
use PhpZip\ZipFile;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class File
{
    public static function cp($source, $dest)
    {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }
        foreach (
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            ) as $item
        ) {
            if ($item->isDir()) {
                $sontDir = $dest . DS . $iterator->getSubPathName();
                if (!is_dir($sontDir)) {
                    mkdir($sontDir, 0755, true);
                }
            } else {
                copy($item, $dest . DS . $iterator->getSubPathName());
            }
        }
    }

    public static function clear($dir)
    {
        if (file_exists($dir)) {
            self::rm($dir);
        }
        mkdir($dir, 0777, true);
    }

    public static function rm($target)
    {
        if (!is_dir($target)) {
            unlink($target);
            return;
        }
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($target, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            $todo = ($file->isDir() ? 'rmdir' : 'unlink');
            $todo($file->getRealPath());
        }
        rmdir($target);
    }

    public static function exist($target)
    {
        # todo window
        if (!str_starts_with($target, '/')) {
            throw new Exception('target must absolute');
        }
        if (!file_exists($target)) {
            throw new Exception('target not exist');
        }
    }

    public static function zip($target, $out = '', $exclude = [])
    {
        self::exist($target);
        $exclude = array_merge(['.git', '.DS_Store', 'Thumbs.db'], $exclude);
        if (empty($out)) {
            $out = $target . '.zip';
        }
        $zipArchive = new ZipArchive;
        $zipArchive->open($out, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($target),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($iterator as $file) {
            if (!$file->isDir()) {
                $filePath     = $file->getRealPath();
                $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', substr($filePath, strlen($target)));
                if (!in_array($file->getFilename(), $exclude)) {
                    $zipArchive->addFile($filePath, $relativePath);
                }
            }
        }
        $zipArchive->close();
    }

    public static function unzip($target, $out = '')
    {
        self::exist($target);
        if (empty($out)) {
            $out = getcwd() . '/' . pathinfo($target)['filename'];
        }
        self::clear($out);

        $zipFile = new ZipFile();
        $zipFile->openFile($target);
        $zipFile->extractTo($out);
        $zipFile->close();
    }

    public static function touch($file, $content = '')
    {
        $info = pathinfo($file);
        if (!is_dir($info['dirname'])) {
            mkdir($info['dirname'], 0777, true);
        }
        file_put_contents($file, $content);
    }
}