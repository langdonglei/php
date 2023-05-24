<?php

namespace langdonglei;

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

    public function near($lng, $lat, $table, $limit = 3,$lng_field = 'lng' , $lat_field = 'lat')
    {
        $sql = <<<EOF
SELECT
	*,
	SQRT(
		POW( 69.1 * ( $lat_field - [ $lat ]), 2 ) + POW( 69.1 * ([ $lng ] - $lng_field ) * COS( $lat_field / 57.3 ), 2 ) 
	) AS distance 
FROM
	$table 
WHERE
	MBRContains (
		LINESTRING (
			POINT ([ $lat ] - 1 / 69.1,
				[ $lng ] - 1 /(
				69.1 * COS([ $lat ]* 0.01745 ))),
			POINT ([ $lat ] + 1 / 69.1,
				[ $lng ] + 1 /(
				69.1 * COS([ $lat ]* 0.01745 ))) 
		),
		POINT ( $lat_field, $lng_field ) 
	) 
ORDER BY
	distance 
	LIMIT $limit;
EOF;
    }

}