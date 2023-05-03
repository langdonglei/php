<?php

namespace langdonglei\util\library;

class Map
{
    const PI           = 3.1415926535898;
    const EARTH_RADIUS = 6378.137;

    function distance($lat1, $lng1, $lat2, $lng2): float
    {
        $radLat1 = $lat1 * (self::PI / 180);
        $radLat2 = $lat2 * (self::PI / 180);

        $a = $radLat1 - $radLat2;
        $b = ($lng1 * (self::PI / 180)) - ($lng2 * (self::PI / 180));

        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
        $s = $s * self::EARTH_RADIUS;
        $s = round($s * 10000) / 10000;
        return floatval(sprintf('%.2f', $s * 1000));
    }

}