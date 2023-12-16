<?php

namespace langdonglei;

class Ping
{
    public static function tcp($ip_and_port, $time_out = 3): bool
    {
        try {
            stream_socket_client('tcp://' . $ip_and_port, $code, $message, $time_out);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}