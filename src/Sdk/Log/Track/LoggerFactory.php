<?php

namespace Zan\Framework\Sdk\Log\Track;

class LoggerFactory
{

    const app = "php";

    /**
     * @param $app
     * @param $module
     * @param string $type
     * @param bool $persistence
     * @param null $topic
     * @return TrackLogger|TrackPersistenceLogger|null
     */
    public static function get($app, $module, $type = 'normal', $persistence = false, $topic = null)
    {
        $logger = null;
        if ($persistence) {
            $logger = new TrackPersistenceLogger($app, $module, $type, $topic);
        } else {
            $logger = new TrackLogger($app, $module, $type, $topic);
        }
        return $logger;
    }
}