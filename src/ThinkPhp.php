<?php

namespace langdonglei;

use Exception;
use think\Validate;
use think\Config;

class ThinkPhp
{
    public static function validate($rule, $message = [])
    {
        $validate = new Validate($rule, $message);
        $validate->check(input());
        if (!$validate) {
            throw new Exception($validate->getError());
        }
    }

    public static function config($name, $exception = true)
    {
        $r = Config($name);
        if (!$r && $exception) {
            throw new Exception("config not found $name");
        }
        return $r;
    }
}