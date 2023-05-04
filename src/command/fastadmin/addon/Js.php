<?php

namespace langdonglei\util\command\fastadmin\addon;


use langdonglei\util\library\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use think\Config;
use think\console\input\Argument;

class Js extends Command
{
    protected function configure()
    {
        $this
            ->addArgument('addon_name', Argument::REQUIRED)
            ->setName('fastadmin:addon:js');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $js_arr  = array_reduce(scandir(ROOT_PATH . '/addons'), function ($carry, $addon) {
            if ($addon === '.' || $addon === '..') {
                return $carry;
            }
            $addon_dir = ROOT_PATH . '/addons/' . $addon;
            if (!is_dir($addon_dir)) {
                return $carry;
            }
            $addon_file = ROOT_PATH . '/addons/' . $addon . '/' . ucfirst($addon) . '.php';
            if (!is_file($addon_file)) {
                return $carry;
            }
            $addon_info = ROOT_PATH . '/addons/' . $addon . '/info.ini';
            if (!is_file($addon_info)) {
                return $carry;
            }
            $info = Config::parse($addon_info);
            if (empty($info['name']) || $info['state'] != 1) {
                return $carry;
            }
            $addon_bootstrap = ROOT_PATH . '/addons/' . $addon . '/bootstrap.js';
            if (!is_file($addon_bootstrap)) {
                return $carry;
            }
            $carry[] = file_get_contents($addon_bootstrap);
            return $carry;
        }, []);
        $js_str  = implode(PHP_EOL, $js_arr);
        $js_file = ROOT_PATH . '/public/assets/js/addons.js';
        File::touch($js_file, <<<EOL
define([], function () {
    $js_str
})
EOL
        );
        return 0;
    }
}