<?php

use GatewayWorker\BusinessWorker;
use GatewayWorker\Gateway;
use GatewayWorker\Register;
use Workerman\Worker;

require_once __DIR__ . '/../../../vendor/autoload.php';
$port                     = '55555';
$register                 = new Register('text://127.0.0.1:1236');
$worker                   = new BusinessWorker();
$worker->name             = 'ChatBusinessWorker';
$worker->count            = 4;
$worker->registerAddress  = '127.0.0.1:1236';
$gateway                  = new Gateway("Websocket://0.0.0.0:$port");
$gateway->name            = 'ChatGateway';
$gateway->count           = 2;
$gateway->lanIp           = '127.0.0.1';
$gateway->startPort       = 2300;
$gateway->pingInterval    = 10;
$gateway->pingData        = '{"type":"ping"}';
$gateway->registerAddress = '127.0.0.1:1236';
Worker::runAll();




