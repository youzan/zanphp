<?php

namespace Zan\Framework\Sdk\Log;

use Zan\Framework\Sdk\Log\Track\LoggerFactory as Track;
use Zan\Framework\Sdk\Log\Track\TrackLogger;
use Zan\Framework\Sdk\Log\Track\TrackPersistenceLogger;

class LoggerFactory extends Track
{
    /**
     * 非持久化日志
     *
     * @param string $module
     *            模块名字
     * @param string $type
     *            业务方自定义type字段日后查询用
     * @return TrackLogger
     */
    public static function getLogger($module, $type = 'normal')
    {
        if (defined("APP_NAME")) {
            $app = APP_NAME;
        } else {
            $app = Track::app;
        }
        return self::get($app, $module, $type);
    }

    /**
     * 持久化日志
     *
     * @param string $module
     *            模块名字
     * @param string $type
     *            业务方自定义type字段日后查询用
     * @return TrackPersistenceLogger
     */
    public static function getPersistenceLogger($module, $type = 'persistence')
    {
        if (defined("APP_NAME")) {
            $app = APP_NAME;
        } else {
            $app = Track::app;
        }
        return self::get($app, $module, $type, true);
    }

    /**
     * 这个方法用的时候找下陈阳
     *
     * @param string $app
     * @param string $module
     * @param string $type
     *            业务方自定义type字段日后查询用
     * @param boolean $persistence
     *            是否持久化
     * @return TrackLogger|TrackPersistenceLogger|null
     */
    public static function getCustomerLogger($app, $module, $type = 'normal', $persistence = false, $topic = null)
    {
        return self::get($app, $module, $type, $persistence, $topic);
    }
}