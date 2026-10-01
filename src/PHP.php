<?php

namespace langdonglei;

class PHP
{
    public static function class_exists($arr)
    {
        foreach ($arr as $item) {
            if (!class_exists($item)) {
                throw new \Exception('vv not found class ' . $item);
            }
        }
    }
}
