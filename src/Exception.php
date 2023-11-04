<?php

namespace langdonglei;

class Exception extends \Exception
{
    public function __construct()
    {
        preg_match('/(?<code>\d+)(?<name>\w+)/', static::class, $matches);
        $message = $matches['name'] ?? '';
        $code    = $matches['code'] ?? 0;
        parent::__construct($message, $code);
    }
}