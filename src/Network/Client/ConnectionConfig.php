<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/18
 * Time: 17:07
 */

namespace Zan\Framework\Network\Client;

use Zan\Framework\Foundation\Exception\System\InvalidArgument;
use Zan\Framework\Utilities\Types\Dir;

class ConnectionConfig {
    private static $configPath = '';
    private static $data = [];
    public static function setConfigPath($path)
    {
        if(!$path || !is_dir($path)) {
            throw new InvalidArgument('invalid path for ConnectionConfig ' . $path);
        }
        $path = Dir::formatPath($path);
        self::$configPath = $path;
    }

    public static function get($key)
    {
    }

    public static function clear()
    {
        self::$data = [];
    }

    private static function getConfigFile()
    {

    }
}