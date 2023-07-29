<?php

namespace langdonglei;

class Gd
{
    public function c($color = 'rand', $alpha = 0)
    {
        global $image;
        if (empty($alpha)) $alpha = mt_rand(0, 127);
        switch ($color) {
            case 'red' :
                $c = imagecolorallocatealpha($image, 255, 0, 0, $alpha);
                break;
            case 'green' :
                $c = imagecolorallocatealpha($image, 0, 255, 0, $alpha);
                break;
            case 'blue' :
                $c = imagecolorallocatealpha($image, 0, 0, 255, $alpha);
                break;
            case 'yellow' :
                $c = imagecolorallocatealpha($image, 255, 255, 0, $alpha);
                break;
            case 'rand' :
                $c = imagecolorallocatealpha($image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255), $alpha);
                break;
            case 'purple' :
                $c = imagecolorallocatealpha($image, 0xff, 0x33, 0xff, $alpha);
                break;
            default :
                $p = '/[0-9,a-f]{6}/i';
                if (preg_match_all($p, $color, $r)) {
                    $xx = str_split($r[0][0], 2);
                    $c = imagecolorallocatealpha($image, hexdec($xx[0]), hexdec($xx[1]), hexdec($xx[2]), $alpha);
                } else {
                    $c = imagecolorallocatealpha($image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255), $alpha);
                }
                break;
        }
        return $c;
    }

    public function thumb($img, $w = 790)
    {
        $i = imagecreatefromstring(file_get_contents($img));
        $ww = imagesx($i);
        if ($ww < $w) return;
        $hh = imagesy($i);

        $nw = $w;
        $nh = $hh * ($w / $ww);
        $ii = imagecreatetruecolor($nw, $nh);
        imagecopyresampled($ii, $i, 0, 0, 0, 0, $nw, $nh, $ww, $hh);
        imagejpeg($ii, $img);
        imagedestroy($i);
        imagedestroy($ii);
    }

    public function thumbs($i, $path = 'mid', $w = 400, $h = 400)
    {
        $ii = getimagesize($i);
        if ($ii[0] > $w) {
            $src = imagecreatefromjpeg($i);
            $sw = $ii[0];
            $sh = $ii[1];

            $h = $h == 0 ? $w / $sw * $sh : $h;
            //建立新的缩略图
            $dst = imagecreatetruecolor($w, $h);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $w, $h, $sw, $sh);
            $new = str_replace('big', $path, $i);
            $dir = pathinfo($new, PATHINFO_DIRNAME);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            imagejpeg($dst, $new);
            imagedestroy($dst);
            imagedestroy($src);
        }
    }

    public function logo($img, $pos = 5)
    {
        $i = imagecreatefromstring(file_get_contents($img));
        $w = imagesx($i);
        $h = imagesy($i);

        $logo = imagecreatefromstring(file_get_contents(dirname(__FILE__) . '/logo.png'));
        $lw = imagesx($logo);
        $lh = imagesy($logo);
        switch ($pos) {
            case 1:
                break;
            case 2:
                break;
            case 3:
                break;
            case 4:
                break;
            case 5:
                $x = ($w - $lw) / 2;
                $y = ($h - $lh) / 2;
                break;
            case 6:
                break;
            case 7:
                break;
            case 8:
                break;
            case 9:
                $x = $w - $lw - 10;
                $y = $h - $lh - 10;
                break;
            default:
                break;
        }
        imagecopy($i, $logo, $x, $y, 0, 0, $lw, $lh);
        imagejpeg($i, $img);
        imagedestroy($i);
        imagedestroy($logo);
    }


    public function s($len = 1)
    {
        $str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $sss = '';
        for ($i = 0; $i < $len; $i++) {
            $pos = mt_rand(0, strlen($str) - 1);
            $sss .= substr($str, $pos, 1);
        }
        return $sss;
    }
}
