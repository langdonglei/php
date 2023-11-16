<?php

namespace langdonglei;

use Exception;

class Str
{
    public static function echo($content, $tag = '', $pad = 27)
    {
        $r        = date('y-m-d H:i:s');
        $function = debug_backtrace()[1]['function'] ?? '';
        if ($function) {
            $r = $r . ' ' . $function;
        }
        if ($tag) {
            $r = $r . ' ' . $tag;
        }
        if (is_array($content)) {
            $content = json_encode($content, JSON_UNESCAPED_UNICODE);
        }
        echo str_pad($r, $pad) . ' => ' . $content . PHP_EOL;
    }

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