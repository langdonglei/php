<?php

namespace langdonglei;

class Ping
{
    public static function tcp($ip_and_port, $timeout = 3): bool
    {
        try {
            stream_socket_client('tcp://' . $ip_and_port, $code, $message, $timeout);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}