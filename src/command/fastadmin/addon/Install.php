<?php

namespace langdonglei\util\command\fastadmin\addon;

use langdonglei\util\command\fastadmin\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Install extends Command
{
    protected function configure()
    {
        $this->setName('fastadmin:addon:install');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->getApplication()->find('fastadmin:addon:zip')->run($input, $output);
        return 0;
    }
}