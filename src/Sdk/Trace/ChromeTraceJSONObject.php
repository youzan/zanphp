<?php
/**
 * Created by IntelliJ IDEA.
 * User: chuxiaofeng
 * Date: 17/4/20
 * Time: 上午2:32
 */

namespace Zan\Framework\Sdk\Trace;


use JsonSerializable;
use Zan\Framework\Foundation\Application;

class ChromeTraceJSONObject implements JsonSerializable
{
    private static $appName;
    private static $hostName;
    private static $ip;
    private static $pid;

    private $json = [
        'version' => '1.0.0',
        'columns' => ['log', 'backtrace', 'type'],
        'rows'    => [],
    ];

    public function __construct()
    {
        $this->initEnv();

        $appInfo = [
            "app"   => self::$appName,
            "host"  => self::$hostName,
            "ip"    => self::$ip,
            "pid"   => self::$pid,
        ];
        $this->addRow("info", [self::$appName, $appInfo]);
    }

    private function initEnv()
    {
        if (self::$appName) {
            return;
        }

        self::$appName = Application::getInstance()->getName();
        self::$hostName = gethostname();
        /** @noinspection PhpUndefinedFunctionInspection */
        self::$ip = nova_get_ip();
        self::$pid = getmypid();
    }

    public function addRow($level, array $logs)
    {
        $backtrace = null; /* 节省header-size */
        $this->json['rows'][] = [$logs, $backtrace, $level];
    }

    public function jsonSerialize()
    {
        return $this->json;
    }
}