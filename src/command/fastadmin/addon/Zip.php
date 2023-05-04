<?php

namespace langdonglei\util\command\fastadmin\addon;


use langdonglei\util\library\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Zip extends Command
{
    protected function configure()
    {
        $this->setName('fastadmin:addon:zip');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        File::unzip(ROOT_PATH . '/abc.zip');
//        File::zip(ROOT_PATH . '/abc');
        return 0;
    }
}