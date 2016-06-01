<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/5/20
 * Time: 16:21
 */

namespace Zan\Framework\Foundation\Core;


class Env {

    private static $data = [];

    public static  function init()
    {
        self::$data['hostname'] = gethostname();
        self::$data['ip'] = gethostbyname(self::$data['hostname']);
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