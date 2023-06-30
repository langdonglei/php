<?php

namespace langdonglei;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Exception;
use GuzzleHttp\Client;
use Throwable;

class Image
{
    /**
     * @throws Exception
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
     * @throws Exception
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
     * @throws Exception
     */
    public static function posterWithUrl($bg_url, $url): string
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
        return self::poster($bg, $qr);
    }

    public static function poster($bg, $qr): string
    {
        imagecopyresized(
            $bg,
            $qr,
            imagesx($bg) / 2 - imagesx($qr) / 2,       # 嵌入图像左上角要放入背景图像的x坐标点
            imagesy($bg) / 2 - imagesy($qr) / 2,       # 嵌入图像左上角要放入背景图像的y坐标点
            0,                                         # 嵌入图像的宽度取值x坐标点
            0,                                         # 嵌入图像的高度取值y坐标点
            imagesx($qr),                              # 嵌入图像在背景图像中要占用的宽度
            imagesy($qr),                              # 嵌入图像在背景图像中要占用的高度
            imagesx($qr),                              # 从嵌入图像坐标点要取的宽度
            imagesy($qr)                               # 从嵌入图像坐标点要取的高度
        );
        $r = File::getSaveName('poster');
        imagepng($bg, $r);
        imagedestroy($bg);
        imagedestroy($qr);
        return $r;
    }
}
