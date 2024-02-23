<?php

namespace langdonglei;

class Wgt
{
    public static function upgrade($input, $dir = '/wgt')
    {
        if (empty($input)) {
            throw new \Exception('参数错误');
        }
        $new = self::new($input, $dir);
        if (strtolower(substr($_SERVER['SERVER_PROTOCOL'], 0, 5)) == 'https') {
            $pre = 'https' . '://' . $_SERVER['HTTP_HOST'];
        } else {
            $pre = 'http' . '://' . $_SERVER['HTTP_HOST'];
        }
        return [
            'upgrade_is' => $input == $new ? 0 : 1,
            'url'        => $pre . $dir . '/' . $new . '.wgt'
        ];
    }

    public static function new($input, $dir)
    {
        $new = '';
        $arr = self::all($input, $dir);
        foreach ($arr as $item) {
            $new = self::bigger($input, $item);
        }
        return $new;
    }

    public static function bigger($one, $two)
    {
        $arr1 = explode('.', $one);
        $arr2 = explode('.', $two);
        if (intval($arr1[0]) !== intval($arr2[0])) {
            return intval($arr1[0]) > intval($arr2[0]) ? $one : $two;
        }
        if (intval($arr1[1]) !== intval($arr2[1])) {
            return intval($arr1[1]) > intval($arr2[1]) ? $one : $two;
        }
        if (intval($arr1[2]) !== intval($arr2[2])) {
            return intval($arr1[2]) > intval($arr2[2]) ? $one : $two;
        }
        return $one;
    }

    public static function all($input, $dir)
    {
        $path = $_SERVER['DOCUMENT_ROOT'] . $dir;
        if (!is_dir($path)) {
            throw new \Exception('站点根下的wgt目录不存在');
        }
        $files = scandir($_SERVER['DOCUMENT_ROOT'] . $dir);
        if (count($files) == 2) {
            throw new \Exception('站点根下的wgt目录空');
        }
        $r = [];
        foreach ($files as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            $item = substr($item, 0, -4);
            if ((int)substr($input, -1) % 2 == 1) {
                if ((int)substr($item, -1) % 2 == 1) {
                    $r[] = $item;
                }
            } else if ((int)substr($input, -1) % 2 == 0) {
                if ((int)substr($item, -1) % 2 == 0) {
                    $r[] = $item;
                }
            } else {
                $r[] = $item;
            }
        }
        return $r;
    }
}