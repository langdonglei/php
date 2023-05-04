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
            ->addArgument('target', Argument::REQUIRED, 'the target you will zip')
            ->setName('fastadmin:addon:zip');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $target = $input->getArgument('target');
        File::unzip($target);
//        File::zip(ROOT_PATH . '/abc');
        return 0;
    }
}