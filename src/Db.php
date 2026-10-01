<?php

namespace langdonglei;

class Db
{
    public static function connect($option = [])
    {
        $env = EnvS::get();
        return new PDO(
            "mysql:host=$env[hostname];dbname=$env[database]",
            $env['username'],
            $env['password'],
            $option
        );
    }

    public static function field_exist($table, $field): bool
    {
        $row = Db::query("show full columns from $table");
        $arr = array_column($row, 'Field');
        return in_array($field, $arr);
    }

    public static function field_add($table, $field, $type = 'int')
    {
        if (!self::field_exist($table, $field)) {
            Db::execute("alter table $table add column $field $type");
        }
    }
}
