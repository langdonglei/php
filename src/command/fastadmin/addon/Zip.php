<?php

namespace langdonglei\util\command\fastadmin\addon;

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
        $output->writeln('hi');
        return 0;
    }
}