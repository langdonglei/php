<?php

namespace langdonglei;

class Think
{
    public static function validate($rule, $data = [], $message = []): array
    {
        if (!class_exists('\think\exception\HttpResponseException')) {
            throw new \Exception('vv not found class \think\exception\HttpResponseException');
        }
        if (!class_exists('\think\Request')) {
            throw new \Exception('vv not found class \think\Request');
        }
        if (!class_exists('\think\Response')) {
            throw new \Exception('vv not found class \think\Response');
        }
        if (!class_exists('\think\Validate')) {
            throw new \Exception('vv not found class \think\Validate');
        }
        $request  = \think\Request::instance();
        $validate = new \think\Validate();
        if (!$data) {
            $data = $request->param();
        }
        $validate->rule($rule)->message($message)->check($data);
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
            if (in_array('array', $v) && !is_array($data[$k] ?? [])) {
                dd('需要处理');
            } else {
                $value = $data[$k] ?? '';
                if (is_string($value)) {
                    $value = trim($value); // todo 不知道为什么后面会有空格 暂时先trim一下
                }
                $r[$k] = $value;
            }
        }
        return $r;
    }

    public static function env($name, $exception = true)
    {
        if (!class_exists('\think\Env')) {
            throw new \Exception('vv not found class \think\Env');
        }

        $r = \think\Env::get($name);
        if ($exception && !$r) {
            throw new \Exception('vv not found env ' . $name);
        }

        return $r;
    }

    public static function config($name, $exception = true)
    {
        if (!class_exists('\think\Config')) {
            throw new \Exception('vv not found class \think\Config');
        }

        $r = \think\Config($name);
        if ($exception && !$r) {
            throw new \Exception('vv not found config ' . $name);
        }

        return $r;
    }

    public static function ok($data = null, $header = [])
    {
        PHP::class_exists([
            '\think\Request',
            '\think\exception\HttpResponseException',
        ]);

        throw new \think\exception\HttpResponseException(\think\Response::create([
            'code' => 1,
            'msg'  => '',
            'time' => \think\Request::instance()->server('REQUEST_TIME'),
            'data' => $data,
        ], 'json')->header($header));
    }

    public static function ng($msg = '', $header = [])
    {
        PHP::class_exists([
            '\think\Request',
            '\think\exception\HttpResponseException',
        ]);

        throw new \think\exception\HttpResponseException(\think\Response::create([
            'code' => 0,
            'msg'  => $msg,
            'time' => \think\Request::instance()->server('REQUEST_TIME'),
            'data' => [],
        ], 'json')->header($header));
    }
}