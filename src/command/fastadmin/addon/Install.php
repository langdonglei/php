<?php

namespace langdonglei\util\command\fastadmin\addon;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use think\console\input\Argument;

class Install extends Command
{
    protected function configure()
    {
        $this
            ->addArgument('addon_name', Argument::REQUIRED)
            ->setName('fastadmin:addon:install');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $addon_name = $input->getArgument('addon_name');
        $addon_path = ROOT_PATH . '/addons/' . $addon_name;
        $class_name = ucfirst($addon_name);

        include $addon_path . '/' . $class_name . '.php';
        $class = 'addons\\' . $addon_name . '\\' . $class_name;
        $class = new $class;
        $class->install();
        $class->enable();

        return 0;
    }
}