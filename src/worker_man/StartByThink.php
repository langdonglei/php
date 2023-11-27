<?php

namespace langdonglei\worker_man;

use Exception;
use GatewayWorker\BusinessWorker;
use GatewayWorker\Gateway;
use GatewayWorker\Register;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use Workerman\Timer;
use Workerman\Worker;

class StartByThink extends Command
{
    protected function configure()
    {
        $this->setName('worker')
            ->addArgument('action', Argument::REQUIRED)
            ->addOption('demon', '-d', Option::VALUE_NONE);
    }

    protected function execute(Input $input, Output $output)
    {
        $handler = \vv\Worker::class;
        if (!class_exists($handler)) {
            throw new Exception('handler ' . $handler . '不存在');
        }

        global $argv;
        $argv[1] = $input->getArgument('action');
        $argv[2] = $input->getOption('demon') ? '-d' : '';

        new Register('text://127.0.0.1:1236');

        $business                  = new BusinessWorker();
        $business->name            = 'Business';
        $business->registerAddress = '127.0.0.1:1236';
        $business->eventHandler    = $handler;

        $gateway                       = new Gateway('websocket://0.0.0.0:4001');
        $gateway->name                 = 'Gateway';
        $gateway->registerAddress      = '127.0.0.1:1236';
        $gateway->pingInterval         = 4444;
        $gateway->pingNotResponseLimit = 2;
        $gateway->pingData             = json_encode(['type' => 'ping']);

        if (!Worker::$daemonize) {
            $monitor                = new Worker();
            $monitor->name          = 'Monitor';
            $monitor->reloadable    = false;
            $monitor->onWorkerStart = function () use ($handler) {
                $event_file = (new ReflectionClass($handler))->getFileName();
                $dir        = substr($event_file, 0, strrpos($event_file, '/'));
                $last_mtime = time();
                Timer::add(1, function () use ($dir, &$last_mtime) {
                    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $file) {
                        if (pathinfo($file, PATHINFO_EXTENSION) == 'php' && $last_mtime < $file->getMTime()) {
                            posix_kill(posix_getppid(), SIGUSR1);
                            $last_mtime = $file->getMTime();
                            break;
                        }
                    }
                });
            };
        }

        Worker::runAll();
    }
}
