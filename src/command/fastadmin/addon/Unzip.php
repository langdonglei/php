<?php

namespace langdonglei\util\command\fastadmin\addon;

use langdonglei\util\library\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use think\console\input\Argument;

class Unzip extends Command
{
    protected function configure()
    {
        $this
            ->addArgument('addon_name', Argument::REQUIRED)
            ->setName('fastadmin:addon:unzip');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $addon_name = $input->getArgument('addon_name');
        $addon_path = ROOT_PATH . '/addons/' . $addon_name;
        File::unzip($addon_path . '.zip', $addon_path);
        return 0;
    }
}