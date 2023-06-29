<?php

namespace langdonglei;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class Image
{
    public static function merge()
    {
        $big   = 'http://1.13.250.157:55555/static/poster_1.jpg';
        $small = 'http://1.13.250.157:55555/static/poster_2.jpg';

        $big_resource   = imagecreatefromstring(file_get_contents($big));
        $small_resource = imagecreatefromstring(file_get_contents($small));

        imagecopy($small_resource, $big_resource, 0, 0, 0, 0, 100, 100);

        imagepng($big_resource);
        echo 333;
    }

    public static function poster($bg_url, $qr_str): string
    {
        $bg = imagecreatefromstring(file_get_contents($bg_url));
        $qr = imagecreatefromstring((new PngWriter())->write(new QrCode($qr_str))->getString());
        imagecopyresized($bg, $qr, imagesx($bg) / 2 - 160, imagesy($bg) / 2 - 160, 0, 0, 320, 320, 320, 320);
        $dir = 'uploads/poster/' . date('Ymd');
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $r = $dir . '/' . md5(microtime(true)) . '.png';
        imagepng($bg, $r);
        return $r;
    }
}