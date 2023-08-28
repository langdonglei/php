<?php

namespace langdonglei;

interface WorkerHandler
{
    public function onWorkerStart();

    public function onMessage();

    /**
     *
     * Workerman\Lib\Timer::add(1, function(){
     *      GatewayWorker\Lib\Gateway::sendToAll(json_encode([
     *          'type'=>'v',
     *          'data'=>111
     *      ]));
     * });
     *
     *
     * !function ws() {
     * const socket = new WebSocket("ws://" + document.domain + ":4110")
     * socket.onclose = function () {
     *      ws()
     * }
     * socket.onmessage = function (e) {
     *      const {type, data} = JSON.parse(e.data)
     *      switch (type) {
     *          case 'ping':
     *              socket.send(JSON.stringify({type: 'pong'}))
     *              break;
     *          }
     *      }
     * }()
     *
     *
     */
}