<?php

namespace websocket;

use Exception;
use GatewayWorker\BusinessWorker;
use GatewayWorker\Gateway;
use GatewayWorker\Register;
use langdonglei\command\websocket\Handler;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use Throwable;
use Workerman\Worker;

class ServerByThink extends Command
{
    protected function configure()
    {
        $this->setName('worker-man')->addArgument('action', Argument::REQUIRED)->addArgument('handler', Argument::REQUIRED)
            ->addOption('demon', '-d', Option::VALUE_NONE)
            ->addOption('port', '-p', Option::VALUE_OPTIONAL, '', 4120)
            ->addOption('text', '-t', Option::VALUE_OPTIONAL, '', 4110);
    }

    /**
     * @throws Throwable
     */
    protected function execute(Input $input, Output $output)
    {
        $handler = $input->getArgument('handler');
        if (!class_exists($handler)) {
            throw new Exception('handler ' . $handler . '不存在');
        }
        $port = $input->getOption('port');
        $text = $input->getOption('text');
        global $argv;
        $argv[1] = $input->getArgument('action');
        $argv[2] = $input->getOption('demon') ? '-d' : '';
        new Register('text://127.0.0.1:' . $text);
        $worker                        = new BusinessWorker();
        $worker->registerAddress       = '127.0.0.1:' . $text;
        $worker->eventHandler          = $handler;
        $gateway                       = new Gateway('websocket://0.0.0.0:' . $port);
        $gateway->registerAddress      = '127.0.0.1:' . $text;
        $gateway->pingInterval         = 1;
        $gateway->pingNotResponseLimit = 11;
        $gateway->pingData             = json_encode(['type' => 'ping']);
        Worker::runAll();
    }
}
