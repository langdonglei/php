<?php

namespace langdonglei;

class Exception extends \Exception
{
    const MESSAGE = '';

    public function __construct()
    {
        preg_match('/(?<code>\d+)$/', static::class, $matches);
        $code    = $matches['code'];
        $message = static::MESSAGE;
        parent::__construct($message, $code);
    }
}