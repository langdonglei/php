<?php

namespace langdonglei;

use Exception;

class Think
{
    public static function validate($rule, $message = []): array
    {
        if (!class_exists('\think\exception\HttpResponseException')) {
            throw new Exception('vv not found class \think\exception\HttpResponseException');
        }
        if (!class_exists('\think\Request')) {
            throw new Exception('vv not found class \think\Request');
        }
        if (!class_exists('\think\Response')) {
            throw new Exception('vv not found class \think\Response');
        }
        if (!class_exists('\think\Validate\Validate')) {
            throw new Exception('vv not found class \think\Validate\Validate');
        }

        $request  = \think\Request::instance();
        $validate = new \think\Validate\Validate();

        $validate->rule($rule)->message($message)->check($request->param());
        $error = $validate->getError();
        if ($error) {
            throw new \think\exception\HttpResponseException(\think\Response::create([
                'code' => 0,
                'msg'  => $error,
                'time' => $request->server('REQUEST_TIME'),
                'data' => null,
            ], 'json'));
        }

        $r = [];
        foreach ($rule as $k => $v) {
            if (!is_array($v)) {
                $v = [$v];
            }
            if (in_array('array', $v)) {
                $r[$k] = $request->param($k . '/a');
            } else {
                $r[$k] = $request->param($k);
            }
        }

        return $r;
    }

    public static function env($name, $exception = true)
    {
        if (!class_exists('\think\Env')) {
            throw new Exception('vv not found class \think\Env');
        }

        $r = \think\Env::get($name);
        if ($exception && !$r) {
            throw new Exception('vv not found env ' . $name);
        }

        return $r;
    }

    public static function config($name, $exception = true)
    {
        if (!class_exists('\think\Config')) {
            throw new Exception('vv not found class \think\Config');
        }

        $r = \think\Config($name);
        if ($exception && !$r) {
            throw new Exception('vv not found config ' . $name);
        }

        return $r;
    }
}