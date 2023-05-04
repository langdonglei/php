<?php

namespace langdonglei\util\command\fastadmin\addon;

use langdonglei\util\library\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use think\console\input\Argument;

class Copy extends Command
{
    protected function configure()
    {
        $this
            ->addArgument('addon_name', Argument::REQUIRED)
            ->setName('fastadmin:addon:copy');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $addon_name = $input->getArgument('addon_name');
        $addon_path = ROOT_PATH . '/addons/' . $addon_name;

        $assets = $addon_path . '/assets';
        if (is_dir($assets)) {
            File::cp($assets, ROOT_PATH . '/public/assets/addons/' . $addon_name);
        }

        $application = $addon_path . '/application';
        if (is_dir($application)) {
            File::cp($application, ROOT_PATH . '/application');
        }

        return 0;
    }
}