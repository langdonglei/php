<?php

namespace langdonglei;

class ThinkPhp
{
    public static function validate($rule, $message = [])
    {
        $validate = new \think\Validate($rule, $message);
        $validate->check(input());
        if (!$validate) {
            throw new \Exception($validate->getError());
        }
    }
}