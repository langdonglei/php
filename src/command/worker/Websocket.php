<?php

namespace langdonglei\command\worker;

use GatewayWorker\BusinessWorker;
use GatewayWorker\Gateway;
use GatewayWorker\Register;
use Workerman\Worker;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;

class Websocket extends Command
{
    protected function configure()
    {
        $this->setName('v:websocket')->addArgument('action', Argument::OPTIONAL, '', 'restart');
    }

    protected function execute(Input $input, Output $output)
    {
        global $argv;
        $argv[1] = $input->getArgument('action');
        $argv[2] = '-d';

        new Register('text://127.0.0.1:1236');
        $business                  = new BusinessWorker();
        $business->name            = 'Business';
        $business->registerAddress = '127.0.0.1:1236';
        $business->eventHandler    = get_class(new class {
            public static function onConnect($client_id)
            {
                \GatewayWorker\Lib\Gateway::sendToCurrentClient(json_encode([
                    'type'      => 'init',
                    'client_id' => $client_id
                ]));
            }

            public static function onMessage()
            {
            }
        });

        // wss服务证书
        // $context = array(
        //     'ssl' => array(
        //         // 请使用绝对路径
        //         'local_cert'	=> '/www/wwwroot/wanlshop/addons/wanlshop/library/GatewayWorker/ssl/chat.pem', // 也可以是crt文件
        //         'local_pk'		=> '/www/wwwroot/wanlshop/addons/wanlshop/library/GatewayWorker/ssl/chat.key',
        //         'verify_peer'	=> false,
        //         // 'allow_self_signed' => true, //如果是自签名证书需要开启此选项
        //     )
        // );

        $gateway                       = new Gateway("websocket://0.0.0.0:1237");
        $gateway->name                 = 'Gateway';
        $gateway->registerAddress      = '127.0.0.1:1236';
        $gateway->pingInterval         = 4;
        $gateway->pingNotResponseLimit = 2;
        $gateway->pingData             = json_encode(['type' => 'ping']);
        // 本机ip,分布式部署时使用内网ip
        $gateway->lanIp = '127.0.0.1';
        // $gateway->transport = 'ssl';

        // Worker::$logFile = getcwd() . '/worker.log';
        Worker::$pidFile = '/var/run/worker.pid';
        Worker::runAll();
    }
}
