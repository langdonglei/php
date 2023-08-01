<?php

namespace langdonglei;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Exception;
use GuzzleHttp\Client;
use Throwable;

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
                    $c  = imagecolorallocatealpha($image, hexdec($xx[0]), hexdec($xx[1]), hexdec($xx[2]), $alpha);
                } else {
                    $c = imagecolorallocatealpha($image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255), $alpha);
                }
                break;
        }
        return $c;
    }

    public function thumb($img, $w = 790)
    {
        $i  = imagecreatefromstring(file_get_contents($img));
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
            $sw  = $ii[0];
            $sh  = $ii[1];

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
        $lw   = imagesx($logo);
        $lh   = imagesy($logo);
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

    /**
     * @throws Throwable
     */
    public static function posterWithContent($bg_url, $content): string
    {
        $client = new Client(['http_errors' => false]);
        try {
            $bg = $client->get($bg_url)->getBody()->getContents();
            $bg = @imagecreatefromstring($bg);
            if (!is_resource($bg)) {
                throw new Exception();
            }
        } catch (Throwable $e) {
            throw new Exception('没有从参数一获取到资源');
        }
        try {
            $qr = @imagecreatefromstring($content);
            if (!is_resource($qr)) {
                throw new Exception();
            }
        } catch (Throwable $e) {
            throw new Exception('没有从参数二获取到资源');
        }
        return self::poster($bg, $qr);
    }

    /**
     * @throws Throwable
     * composer require endroid/qrcode
     */
    public static function posterWithStr($bg_url, $str)
    {
        $client = new Client(['http_errors' => false]);
        try {
            $bg = $client->get($bg_url)->getBody()->getContents();
            $bg = @imagecreatefromstring($bg);
            if (!is_resource($bg)) {
                throw new Exception();
            }
        } catch (Throwable $e) {
            throw new Exception('没有从参数一获取到资源');
        }
        try {
            $qr = @imagecreatefromstring((new PngWriter())->write(new QrCode($str))->getString());
            if (!is_resource($qr)) {
                throw new Exception();
            }
        } catch (Throwable $e) {
            throw new Exception('没有从参数二获取到资源');
        }
    }

    /**
     * @throws Throwable
     */
    public static function posterWithUrl($bg_url, $url, $x = '', $y = ''): string
    {
        $client = new Client(['http_errors' => false]);
        try {
            $bg = $client->get($bg_url)->getBody()->getContents();
            $bg = @imagecreatefromstring($bg);
            if (!is_resource($bg)) {
                throw new Exception();
            }
        } catch (Throwable $e) {
            throw new Exception('没有从参数一获取到资源');
        }
        try {
            $qr = $client->get($url)->getBody()->getContents();
            $qr = @imagecreatefromstring($qr);
            if (!is_resource($qr)) {
                throw new Exception();
            }
        } catch (Throwable $e) {
            throw new Exception('没有从参数二获取到资源');
        }
        return self::poster($bg, $qr, $x, $y);
    }

    public static function poster($bg, $qr, $x = '', $y = ''): string
    {
        # 默认放正中间
        if ($x == '' || $y == '') {
            $x = imagesx($bg) / 2 - imagesx($qr) / 2;
            $y = imagesy($bg) / 2 - imagesy($qr) / 2;
        }
        imagecopyresized(
            $bg,
            $qr,
            $x,       # 嵌入图像左上角要放入背景图像的x坐标点
            $y,       # 嵌入图像左上角要放入背景图像的y坐标点
            0,                                         # 嵌入图像的宽度取值x坐标点
            0,                                         # 嵌入图像的高度取值y坐标点
            imagesx($qr),                              # 嵌入图像在背景图像中要占用的宽度
            imagesy($qr),                              # 嵌入图像在背景图像中要占用的高度
            imagesx($qr),                              # 从嵌入图像坐标点要取的宽度
            imagesy($qr)                               # 从嵌入图像坐标点要取的高度
        );
        $r = File::generateFileName('poster', '.png');
        imagepng($bg, $r);
        imagedestroy($bg);
        imagedestroy($qr);
        return $r;
    }
}
