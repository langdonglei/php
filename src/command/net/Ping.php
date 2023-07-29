<?php

namespace langdonglei\command\net;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;

class Ping extends Command
{
    protected function configure()
    {
        $this->setName('ping')->addArgument('ip', Argument::REQUIRED);
    }

    protected function execute(Input $input, Output $output)
    {
        $ip = $input->getArgument('ip');
        if (strtolower(PHP_OS) == 'linux') {
            $str = `ping $ip -c 1 -w 1`;
        } else {
            $str = `ping $ip -n 1 -w 1`;
        }
        preg_match('/\s(?P<loss>\d{1,3})%/u', $str, $matches);
        $output->write($matches['loss']);
    }
}