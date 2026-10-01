<?php

namespace langdonglei;


namespace app\zz\service;

use zz\model\AreaM;
use zz\model\RegionM;
use Redis;
use think\Cache;
use think\Db;

class Area
{
    public static function tree(): array
    {
        $data = AreaM::column('id, pid, name, level', 'id');
        $tree = [];
        foreach ($data as $p => $province) {
            if ($province['level'] === 1) {    // 省份
                $tree[$province['id']] = $province;
                unset($data[$p]);
                foreach ($data as $c => $city) {
                    if ($city['level'] === 2 && $city['pid'] === $province['id']) {    // 城市
                        $tree[$province['id']]['city'][$city['id']] = $city;
                        unset($data[$c]);
                        foreach ($data as $a => $area) {
                            if ($area['level'] === 3 && $area['pid'] === $city['id']) {    // 地区
                                $tree[$province['id']]['city'][$city['id']]['area'][$area['id']] = $area;
                                unset($data[$a]);
                            }
                        }
                    }
                }
            }
        }
        return $tree;
    }

    public static function getAreaFromLngLat($lng, $lat, $level = 3)
    {
        $namearr = [1 => 'geo:province', 2 => 'geo:city', 3 => 'geo:district'];
        $rangearr = [1 => 15000, 2 => 1000, 3 => 200];
        $geoname = isset($namearr[$level]) ? $namearr[$level] : $namearr[3];
        $georange = isset($rangearr[$level]) ? $rangearr[$level] : $rangearr[3];
        $redis = Cache::store('area')->handler();
        $georadiuslist = [];
        if (method_exists($redis, 'georadius')) {
            $georadiuslist = $redis->georadius($geoname, $lng, $lat, $georange, 'km', ['WITHDIST', 'COUNT' => 5, 'ASC']);
        }
        if ($georadiuslist) {
            list($id, $distance) = $georadiuslist[0];
        }
        return isset($id) && $id ? $id : 3;
    }

    public static function getAreaId($longitude, $latitude)
    {
        if (empty($input['offset'])) {
            $input['offset'] = '0.1';
        }
        $where = [
            'longitude' => ['between', [bcsub($input['longitude'], $input['offset'], 5), bcadd($input['longitude'], $input['offset'], 5)]],
            'latitude' => ['between', [bcsub($input['latitude'], $input['offset'], 5), bcadd($input['latitude'], $input['offset'], 5)]]
        ];
        $list = RegionM::where($where)->select();
    }

    public static function import()
    {
        $redis = new Redis;
        $options = [
            'host' => '127.0.0.1',
            'port' => 6379,
            'password' => '',
            'select' => 4,
            'timeout' => 0,
            'expire' => 0,
            'persistent' => false,
            'userprefix' => 'up:',
            'tokenprefix' => 'tp:',
        ];
//        $redis->pconnect($options['host'], $options['port'], $options['timeout'], 'persistent_id_' . $options['select']);
        $redis->connect($options['host'], $options['port'], $options['timeout']);//非持久化
        $redis->select(4);


        $list1 = Db::name('area')->where('level', 1)->select();
        foreach ($list1 as $val) {
            $redis->geoadd('geo:province', (float)$val['lng'], (float)$val['lat'], $val['id']);
        }

        $list2 = Db::name('area')->where('level', 2)->select();
        foreach ($list2 as $val) {
            $redis->geoadd('geo:city', (float)$val['lng'], (float)$val['lat'], $val['id']);
        }

        $list3 = Db::name('area')->where('level', 3)->select();
        foreach ($list3 as $val) {
            $redis->geoadd('geo:district', (float)$val['lng'], (float)$val['lat'], $val['id']);
        }
        echo "导入成功";
    }
}
