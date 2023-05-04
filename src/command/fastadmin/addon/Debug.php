<?php

namespace langdonglei\util\command\fastadmin\addon;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use think\console\input\Argument;

class Debug extends Command
{
    protected function configure()
    {
        $this
            ->addArgument('addon_name', Argument::REQUIRED)
            ->setName('fastadmin:addon:debug');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->getApplication()->find('fastadmin:addon:zip')->run($input, $output);
        $this->getApplication()->find('fastadmin:addon:unzip')->run($input, $output);
        $this->getApplication()->find('fastadmin:addon:sql')->run($input, $output);
        $this->getApplication()->find('fastadmin:addon:copy')->run($input, $output);
        $this->getApplication()->find('fastadmin:addon:js')->run($input, $output);
        $this->getApplication()->find('fastadmin:addon:install')->run($input, $output);
        return 0;
    }
}