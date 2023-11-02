<?php

namespace langdonglei;

class Exception extends \Exception
{
    public function __construct()
    {
        preg_match('/(?<code>\d+)(?<name>\w)$/', static::class, $matches);
        $message = $matches['name'];
        $code    = $matches['code'];
        parent::__construct($message, $code);
    }
}