<?php

namespace langdonglei\util\command\fastadmin\addon;

use langdonglei\util\library\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use think\console\input\Argument;
use think\Db;

class Sql extends Command
{
    protected function configure()
    {
        $this
            ->addArgument('addon_name', Argument::REQUIRED)
            ->setName('fastadmin:addon:sql');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $addon_name = $input->getArgument('addon_name');
        $addon_path = ROOT_PATH . '/addons/' . $addon_name;
        $sql        = $addon_path . '/install.sql';
        if (is_file($sql)) {
            $lines = file($sql);
            $sql   = '';
            foreach ($lines as $line) {
                if (substr($line, 0, 2) == '--' || $line == '' || substr($line, 0, 2) == '/*') {
                    continue;
                }
                $sql .= $line;
                if (substr(trim($line), -1, 1) == ';') {
                    $sql = str_ireplace('__PREFIX__', config('database.prefix'), $sql);
                    Db::getPdo()->exec($sql);
                    $sql = '';
                }
            }
        }
        return 0;
    }
}