<?php

namespace langdonglei;

class Str
{
    public static function domain($str)
    {
        if (!$str || str_starts_with($str, 'http') || str_starts_with($str, 'data:image')) {
            return $str;
        }
        if (function_exists('get_addon_config')) {
            $config = get_addon_config('alioss');
            $domain = rtrim($config['cdnurl'] ?? '', '/');
        }
        if (empty($domain)) {
            $domain = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
        }
        return $domain . '/' . ltrim($str, '/');
    }
}