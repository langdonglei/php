<?php

namespace langdonglei\command\websocket;

use GatewayWorker\Lib\Gateway;
use Workerman\Lib\Timer;

class Handler
{
    public static function onWorkerStart()
    {
        Timer::add(1, function(){
            Gateway::sendToAll(json_encode([
                'type'=>'v',
                'data'=>111
            ]));
        });
    }
    public static function onMessage($client_id, $message)
    {

    }
}