<?php

namespace langdonglei;

use Exception;
use PhpZip\ZipFile;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;
use PHPExcel;
use PHPExcel_IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Throwable;

class File
{
    public static function generateFileName($flag = 'tmp', $ext = ''): string
    {
        $dir = "uploads/$flag/" . date('Ymd');
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        return $dir . '/' . md5(microtime(true)) . $ext;
    }

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

    public static function zip($dir, $package = '')
    {
        if (!is_dir($dir)) {
            throw new Exception('zip dir not exist');
        }
        if (!$package) {
            $package = $dir . '.zip';
        }
        $handler = new ZipArchive;
        $handler->open($package, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::LEAVES_ONLY) as $file) {
            if (!$file->isDir()) {
                $real_path     = $file->getRealPath();
                // todo 如果绝对路径中有多个同名 dir
                $relative_path = substr($real_path, strpos($real_path, $dir) + 1 + strlen($dir));
                $handler->addFile($real_path, $relative_path);
            }
        }
        $handler->close();
    }

//    public static function unzip($target, $out = '')
//    {
//        self::exist($target);
//        if (empty($out)) {
//            $out = getcwd() . '/' . pathinfo($target)['filename'];
//        }
//        self::clear($out);
//
//        $zipFile = new ZipFile();
//        $zipFile->openFile($target);
//        $zipFile->extractTo($out);
//        $zipFile->close();
//    }

    public static function touch($file, $content = '')
    {
        $info = pathinfo($file);
        if (!is_dir($info['dirname'])) {
            mkdir($info['dirname'], 0777, true);
        }
        file_put_contents($file, $content);
    }

    public static function upload($dir, $tag = 'vv'): array
    {
        if (empty($_FILES[$tag])) {
            throw new Exception("$tag empty");
        }
        $files = (array)$_FILES['vv']['tmp_name'];
        return array_reduce($files, function ($carry, $item) use ($dir) {
            $url = File::generateFileName($dir);
            move_uploaded_file($item, $url);
            $carry[] = $url;
            return $carry;
        }, []);
    }

    /**
     * @throws Throwable
     */
    public static function excel_import($absolute_path, $fields, $row_start = 2, $model = null)
    {
        if (!str_starts_with($absolute_path, '/')) {
            throw new Exception('参数一必须是绝对路径');
        }
        $ext = pathinfo($absolute_path, PATHINFO_EXTENSION);
        if (!in_array($ext, ['csv', 'xls', 'xlsx'])) {
            throw new Exception('未知的数据格式');
        }
        if ($ext === 'csv') {
            $file = fopen($absolute_path, 'r');
            $path = tempnam(sys_get_temp_dir(), 'import_csv');
            $fp   = fopen($path, "w");
            $n    = 0;
            while ($line = fgets($file)) {
                $line     = rtrim($line, "\n\r\0");
                $encoding = mb_detect_encoding($line, ['utf-8', 'gbk', 'latin1', 'big5']);
                if ($encoding != 'utf-8') {
                    $line = mb_convert_encoding($line, 'utf-8', $encoding);
                }
                if ($n == 0 || preg_match('/^".*"$/', $line)) {
                    fwrite($fp, $line . "\n");
                } else {
                    fwrite($fp, '"' . str_replace(['"', ','], ['""', '","'], $line) . "\"\n");
                }
                $n++;
            }
            fclose($file) || fclose($fp);
            $reader = new Csv();
        } elseif ($ext === 'xls') {
            $reader = new Xls();
        } else {
            $reader = new Xlsx();
        }
        $data  = [];
        $sheet = $reader->load($absolute_path)->getSheet(0);
        $row   = $sheet->getHighestRow();
        $col   = count($fields);
        for ($r = $row_start; $r <= $row; $r++) {
            $item = [];
            for ($c = 1; $c <= $col; $c++) {
                $item[] = $sheet->getCellByColumnAndRow($c, $r)->getValue() ?? '';
            }
            $data[] = array_combine($fields, $item);
        }
        if ($model) {
            $model->saveAll($data);
        } else {
            return $data;
        }
    }

    public static function excel_output($array)
    {
        header('Cache-Control: max-age=0');
        header('Content-Disposition: attachment;filename="excel.xlsx"');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        include_once getcwd() . '/vendor/PHPExcel/PHPExcel.php';
        $excel = new PHPExcel();
        $excel->getActiveSheet()->fromArray($array);
        PHPExcel_IOFactory::createWriter($excel, 'Excel2007')->save('php://output');
    }

}