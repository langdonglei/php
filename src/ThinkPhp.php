<?php

namespace langdonglei;

use Exception;
use think\exception\HttpResponseException;
use think\Request;
use think\Response;
use think\Validate;
use think\Config;

class ThinkPHP
{
    public static function validate($rule, $message = [])
    {
        $validate = new Validate($rule, $message);
        $validate->check(Request::instance()->param());
        $error = $validate->getError();
        if ($error) {
            throw new HttpResponseException(Response::create([
                'code' => 0,
                'msg'  => $error,
                'time' => Request::instance()->server('REQUEST_TIME'),
                'data' => null
            ], 'json'));
        }
        $r = [];
        foreach (array_keys($rule) as $key) {
            $r[$key] = Request::instance()->param($key);
        }
        return $r;
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