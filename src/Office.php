<?php

namespace langdonglei;

use Exception;
use PHPExcel;
use PHPExcel_IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Throwable;

class Office
{
    /**
     * @throws Throwable
     */
    public function excel_import($target, $fields, $model = null)
    {
        $ext = pathinfo($target, PATHINFO_EXTENSION);
        if (!in_array($ext, ['csv', 'xls', 'xlsx'])) {
            throw new Exception('未知的数据格式');
        }
        if ($ext === 'csv') {
            $file = fopen($target, 'r');
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
        $data = [];
        try {
            $sheet = $reader->load($target)->getSheet(0);
            $row   = $sheet->getHighestRow();
            $col   = count($fields);
            for ($r = 2; $r <= $row; $r++) {
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
        } catch (Throwable $e) {
            throw new Exception('操作失败 请按照模板中的格式进行导入');
        }
    }


    public function excel_output($array)
    {
        header('Cache-Control: max-age=0');
        header('Content-Disposition: attachment;filename="excel.xlsx"');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        include_once getcwd() . '/vendor/PHPExcel/PHPExcel.php';
        $excel = new PHPExcel();
        $excel->getActiveSheet()->fromArray($array);
        PHPExcel_IOFactory::createWriter($excel, 'Excel2007')->save('php://output');
    }

    public function test($p)
    {
        $ext = pathinfo($p, PATHINFO_EXTENSION);
        if (!in_array($ext, ['csv', 'xls', 'xlsx'])) {
            throw new Exception('未知的数据格式');
        }
        if ($ext === 'csv') {
            $file = fopen($p, 'r');
            $p    = tempnam(sys_get_temp_dir(), 'import_csv');
            $fp   = fopen($p, "w");
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
        return $reader;
    }
}