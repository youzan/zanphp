<?php
namespace Zan\Framework\Sdk\Uuid;

use Zan\Framework\Store\Facade\Cache;

class RedisUuid extends UuidAbstract
{
    public static function getInstance()
    {
        if (! self::$instance) {
            self::$instance = new self();

            $appendConfig = [
                'connection' => 'redis.uuid',
                'exp' => '1'
            ];
            $appendConfig['key'] = 'step';
            Cache::appendConfigMapByConfigKey('uuid.step', $appendConfig);

            $appendConfig['key'] = 'serialid';
            Cache::appendConfigMapByConfigKey('uuid.serialid', $appendConfig);

            $appendConfig['key'] = 'snowflake';
            Cache::appendConfigMapByConfigKey('uuid.snowflake', $appendConfig);

            $appendConfig['key'] = 'objectid';
            Cache::appendConfigMapByConfigKey('uuid.objectid', $appendConfig);
        }

        return self::$instance;
    }

    public function get($tableName)
    {
        yield Cache::hGet('uuid.step', [], $tableName);
    }

    public function getSerialId()
    {
        yield Cache::hGet('uuid.serialid', []);
    }

    public function getSnowflake()
    {
        yield Cache::hGet('uuid.snowflake', []);
    }

    public function getObjectId()
    {
        yield Cache::hGet('uuid.objectid', []);
    }

    private function backToInt($string)
    {
        return (int)substr($string,1);
    }
}