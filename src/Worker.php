<?php

namespace langdonglei;

use Exception;
use GatewayWorker\Lib\Gateway;

class Worker
{
    public static function reply($arr, $code = 1)
    {
        $r = json_encode(['code' => $code] + $arr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        Str::echo($r);
        Gateway::sendToCurrentClient($r);
    }

    public static function yell($arr, $group_id)
    {
        $r = json_encode(['code' => 1] + $arr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        Str::echo($r);
        Gateway::sendToGroup($group_id, $r);
    }

    public static function exception($error, $arr)
    {
        $r = json_encode(['error' => $error] + $arr);
        throw new Exception($r);
    }
}