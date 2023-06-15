<?php

namespace langdonglei;

class Str
{
    public static function domain($str)
    {
        if (!$str || str_starts_with($str, 'http') || str_starts_with($str, 'data:image')) {
            return $str;
        }
        $scheme = $_SERVER['REQUEST_SCHEME'];
        $host   = $_SERVER['HTTP_HOST'];
        if (!$scheme || !$host) {
            return $str;
        }
        return $scheme . '://' . $host . '/' . ltrim($str, '/');
    }
}