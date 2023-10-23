<?php

namespace langdonglei;

use Exception;

class Str
{
    public static function domain($str, $must_self = false)
    {
        if (!$str || str_starts_with($str, 'http') || str_starts_with($str, 'data:image')) {
            return $str;
        }
        if (!$must_self && function_exists('get_addon_config')) {
            $config = get_addon_config('alioss');
            $domain = rtrim($config['cdnurl'] ?? '', '/');
        }
        if (empty($domain)) {
            $domain = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
        }
        return $domain . '/' . ltrim($str, '/');
    }

    public static function env($name, $exception = true, $prefix = 'PHP_')
    {
        $r = getenv($prefix . strtoupper(str_replace('.', '_', $name)));
        if ($r === false && $exception) {
            throw new Exception("env not found $prefix$name");
        }
        if ('false' === $r) {
            $r = false;
        } else if ('true' === $r) {
            $r = true;
        }
        return $r;
    }
}