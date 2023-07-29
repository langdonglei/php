<?php

namespace langdonglei;

use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class Office
{
    public function import()
    {
        $file = $this->request->request('file');
        if (!$file) {
            $this->error(__('Parameter %s can not be empty', 'file'));
        }
        $path = ROOT_PATH . 'public' . $file;
        if (!is_file($path)) {
            $this->error(__('No results were found'));
        }
        $excel = $this->reader($path)->load($path);
        if (!$excel) {
            $this->error('文件加载失败');
        }
        $field = ['sn', 'no', 'name', 'qc', 'cdz', 'zzzj', 'gjd', 'cs', 'zzclip', 'tbq', 'zzbkz'];
        $data  = [];
        $sheet = $excel->getSheet(0);
        $row   = $sheet->getHighestRow();
        $col   = count($field);
        for ($r = 2; $r <= $row; $r++) {
            $item = [];
            for ($c = 1; $c <= $col; $c++) {
                $item[] = $sheet->getCellByColumnAndRow($c, $r)->getValue() ?? '';
            }
            $data[] = array_combine($field, $item);
        }
        $this->model->execute('truncate vv_employee');
        $this->model->saveAll($data);
        $this->success('导入数据' . count($data) . '条');
    }

    private function reader($path)
    {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        if (!in_array($ext, ['csv', 'xls', 'xlsx'])) {
            throw new Exception('未知的数据格式');
        }
        if ($ext === 'csv') {
            $file = fopen($path, 'r');
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
        return $reader;
    }

    public function export()
    {
        header('Cache-Control: max-age=0');
        header('Content-Disposition: attachment;filename="excel.xlsx"');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        include_once getcwd() . '/vendor/PHPExcel/PHPExcel.php';
        $excel = new \PHPExcel();
        $excel->getActiveSheet()->fromArray(m('recode')->where(['id' => ['between', implode(',', i(''))]])->select());
        \PHPExcel_IOFactory::createWriter($excel, 'Excel2007')->save('php://output');
    }
}