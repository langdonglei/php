<?php

namespace langdonglei;

class Image
{
    public static function merge()
    {
        $big = 'http://1.13.250.157:55555/static/poster_1.jpg';
        $small = 'http://1.13.250.157:55555/static/poster_2.jpg';

        $big_resource = imagecreatefromstring(file_get_contents($big));
        $small_resource = imagecreatefromstring(file_get_contents($small));

        imagecopy($small_resource,$big_resource,0,0,0,0,100,100);

        imagepng ($big_resource);
        echo 333;
    }
}