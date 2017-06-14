<?php

namespace Zan\Framework\Foundation\Core;


class Env
{

    private static $data = [];

    public static  function init()
    {
        self::$data['hostname'] = gethostname();
        self::$data['ip'] = nova_get_ip();
        self::$data['pid'] = getmypid();
        self::$data['uid'] = getmyuid();
    }

    public static function get($key, $default=null)
    {
        if(isset(self::$data[$key])){
            return self::$data[$key];
        }

        $result = getenv($key);
        if($result) {
            return $result;
        }

        return $default;
    }

    public static function set($key, $value)
    {
        self::$data[$key] = $value;
    }



}