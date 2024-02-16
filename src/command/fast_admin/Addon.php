<?php

namespace langdonglei\command\fast_admin;

use langdonglei\File;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\VarExporter\VarExporter;
use think\addons\Service;
use think\Cache;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Exception;
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class Addon extends Command
{
    protected function configure()
    {
        $this->setName('vv:addon');
    }

    protected function execute(Input $input, Output $output)
    {
        $conflict = Service::getGlobalFiles('vv', true);
        if ($conflict) {
            dump($conflict);
            $output->ask($input, '发现冲突文件,是否继续?', 'y', function ($anser) {
                if (strtolower($anser) != 'y') {
                    exit();
                }
            });
        }
        $this->copy();
        $this->js();
        $this->php();
        $this->zip();
    }

    private function copy()
    {
        copydirs('addons/vv/assets', 'public/assets/addons/vv');
        copydirs('addons/vv/application', 'application');
        copydirs('addons/vv/public', 'public');
    }

    private function js()
    {
        $r = [];
        foreach (get_addon_list() as $name => $info) {
            $file = ADDON_PATH . $name . DS . 'bootstrap.js';
            if ($info['state'] && is_file($file)) {
                $r[] = file_get_contents($file);
            }
        }
        $js     = ROOT_PATH . str_replace("/", DIRECTORY_SEPARATOR, "public/assets/js/addons.js");
        $handle = fopen($js, 'w');
        if (!$handle) {
            throw new Exception('无法打开文件 ' . $js);
        }
        $tpl = <<<EOD
define([], function () {
    {__JS__}
});
EOD;
        fwrite($handle, str_replace("{__JS__}", implode("\n", $r), $tpl));
        fclose($handle);
        Cache::rm("addons");
        Cache::rm("hooks");
    }

    private function php()
    {
        $php    = APP_PATH . 'extra' . DIRECTORY_SEPARATOR . 'addons.php';
        $config = get_addon_autoload_config(true);
        if (!$config['autoload']) {
            if (!is_really_writable($php)) {
                throw new Exception(__("Unable to open file '%s' for writing", "addons.php"));
            }
            file_put_contents($php, "<?php\n\n" . "return " . VarExporter::export($config) . ";\n", LOCK_EX);
        }
    }

    private function zip()
    {
        $handle = new ZipArchive;
        $handle->open('C:\\Users\\vv\\Desktop\\vv.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $dir = ADDON_PATH . 'vv';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::LEAVES_ONLY) as $file) {
            if (!$file->isDir()) {
                $real_path     = $file->getRealPath();
                $relative_path = substr($real_path, strlen($dir) + 1);
                if (PHP_OS == 'WINNT') {
                    $relative_path = str_replace('\\', '/', $relative_path);
                }
                $handle->addFile($real_path, $relative_path);
            }
        }
        $handle->close();
    }
}