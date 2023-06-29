<?php

namespace langdonglei\command\websocket;

use GatewayWorker\BusinessWorker;
use GatewayWorker\Gateway;
use GatewayWorker\Register;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use Workerman\Worker;

class Index extends Command
{
    protected function configure()
    {
        $this->setName('ws')
            ->addArgument('action', Argument::REQUIRED)
            ->addOption('demon', '-d', Option::VALUE_NONE);
    }

    protected function execute(Input $input, Output $output)
    {
        global $argv;
        $argv[1] = $input->getArgument('action');
        $argv[2] = $input->getOption('demon') ? '-d' : '';
        new Register("text://127.0.0.1:4120");
        $worker = new BusinessWorker();
        $worker->registerAddress = "127.0.0.1:4120";
        $worker->eventHandler = Handler::class;
        $gateway = new Gateway("websocket://0.0.0.0:4110");
        $gateway->registerAddress = "127.0.0.1:4120";
        $gateway->pingInterval = 1;
        $gateway->pingNotResponseLimit = 11;
        $gateway->pingData = json_encode(['type' => 'ping']);
        Worker::runAll();
    }
}

