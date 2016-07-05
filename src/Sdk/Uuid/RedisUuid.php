<?php
namespace Zan\Framework\Sdk\Uuid;

use Zan\Framework\Store\Facade\Cache;

class RedisUuid extends UuidAbstract
{
    public static function getInstance()
    {
        if (! self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function get($tableName)
    {
        $ret = (yield Cache::hGet('uuid.step', $tableName));
        yield $this->backToInt($ret);
    }

    public function getSerialId()
    {
        $ret = (yield Cache::hGet('uuid.serialid',''));
        yield $this->backToInt($ret);
    }

    public function getSnowflake()
    {
        $ret = (yield Cache::hGet('uuid.snowflake',''));
        yield $this->backToInt($ret);
    }

    public function getObjectId()
    {
        yield Cache::hGet('uuid.objectid','');
    }

    private function backToInt($string)
    {
        return (int)substr($string,1);
    }
}