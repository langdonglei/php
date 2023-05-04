<?php

namespace langdonglei\util\command\fastadmin\addon;

use langdonglei\util\library\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use think\console\input\Argument;

class Zip extends Command
{
    protected function configure()
    {
        $this
            ->addArgument('addon_name', Argument::REQUIRED)
            ->setName('fastadmin:addon:zip');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $addon_name = $input->getArgument('addon_name');
        $addon_path = ROOT_PATH . '/addons/' . $addon_name;
//        $info    = parse_ini_file('info.ini');
//        $name    = $info['name'];
//        $version = $info['version'];
//        $zip     = ROOT_PATH . 'runtime/zz/' . $name . '-' . $version . '.zip';
        File::zip($addon_path, $addon_path . '.zip');
        return 0;
    }
}