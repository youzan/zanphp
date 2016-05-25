<?php
/*
 *    Copyright 2012-2016 Youzan, Inc.
 *
 *    Licensed under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an "AS IS" BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
 */
namespace Zan\Framework\Store\Facade;

use RuntimeException;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Connection\ConnectionManager;
use Zan\Framework\Store\NoSQL\Redis\RedisManager;

class Cache {

    private static $redis=null;

    private static $cacheMap = null;

    public static function init(array $cacheMap)
    {
        self::$cacheMap = $cacheMap;
    }

    public static function get($configKey, $keys)
    {
        yield self::getRedisManager($configKey);
        $cacheKey = self::getConfigCacheKey($configKey);
        $realKey = self::getRealKey($cacheKey, $keys);
        if (!empty($realKey)) {
            $result = (yield self::$redis->get($realKey));
            yield $result;
        }
    }

    public static function expire($configKey, $key, $expire=0)
    {
        yield self::getRedisManager($configKey);
        $cacheKey = self::getConfigCacheKey($configKey);
        $realKey = self::getRealKey($cacheKey, $key);
        if (!empty($realKey)) {
            $result = (yield self::$redis->expire($realKey, $expire));
            yield $result;
        }
    }

    public static function set($configKey, $value, $keys)
    {
        yield self::getRedisManager($configKey);
        $cacheKey = self::getConfigCacheKey($configKey);
        $realKey = self::getRealKey($cacheKey, $keys);
        if (!empty($realKey)) {
            $result = (yield self::$redis->set($realKey, $value, $cacheKey['exp']));
            yield $result;
        }
    }

    private static function getRedisConnByConfigKey($configKey)
    {
        $pos= strrpos($configKey, '.');
        $subPath = substr($configKey,0, $pos);
        $config = self::getConfigCacheKey($subPath);
        if(!isset($config['common'])) {
            throw new RuntimeException('connection path config not found');
        }
        return $config['common']['connection'];
    }

    public static function getRedisManager($configKey)
    {
        $conn = (yield ConnectionManager::getInstance()->get(self::getRedisConnByConfigKey($configKey)));
        self::$redis = new RedisManager($conn);
    }

    private static function getRealKey($config, $keys){
        $format = $config['key'];
        if($keys === null){
            return $format;
        }
        if(!is_array($keys)){
            $keys = [$keys];
        }
        $key = call_user_func_array('sprintf', array_merge([$format], $keys));
        return $key;
    }

    private static function getConfigCacheKey($configKey)
    {
        $result = self::$cacheMap;
        $routes = explode('.',$configKey);
        if(empty($routes)){
            return null;
        }
        foreach($routes as $route){
            if(!isset($result[$route])){
                return null;
            }
            $result = &$result[$route];
        }
        return $result;
    }
}