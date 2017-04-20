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
use Zan\Framework\Utilities\Encode\LZ4;

class ChromeTraceJSONObject implements JsonSerializable
{
    private static $appName;
    private static $hostName;
    private static $ip;
    private static $pid;

    private $json = [
        "version" => "1.0.0",
        /**
         * type: chrome::console.{type}
         * log: 日志信息
         * backtrace:
         * trace: 远程信息
         */
        "columns" => ["type", "log", "trace", "backtrace"],
        "rows"    => [],
    ];

    public function __construct(array $json = null)
    {
        $this->initEnv();
        if ($json) {
            $this->json = $json;
        }
    }

    public function addAppInfo()
    {
        $appInfo = [
            "app"   => self::$appName,
            "host"  => self::$hostName,
            "ip"    => self::$ip,
            "pid"   => self::$pid,
        ];
        $this->addRow("info", [self::$appName, $appInfo]);
    }

    public function encode()
    {
        return base64_encode(json_encode($this, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));


        $jsonStr = json_encode($this, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($jsonStr !== false) {
            $lz4Str = LZ4::getInstance()->encode($jsonStr);
            if ($lz4Str !== false) {
                return base64_encode($lz4Str);
            }
        }
        return false;
    }

    public static function decode($raw)
    {
        return new self(json_decode(base64_decode($raw), true));

        $lz4Str = base64_decode($raw);
        if ($lz4Str !== false) {
            $jsonStr = LZ4::getInstance()->decode($lz4Str);
            if ($jsonStr !== false) {
                $json = json_decode($jsonStr, true);
                if (isset($json["columns"]) && isset($json["rows"]) && isset($json["version"])) {
                    return new self($json);
                }
            }
        }
        sys_error("ChromeTrace decode fail, raw=" . strval($raw));
        return new self;
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

    public function addRow($level, array $logs, ChromeTraceJSONObject $trace = null, $backtrace = null)
    {
        $this->json['rows'][] = [$level, $logs, $trace, $backtrace];
    }

    public function jsonSerialize()
    {
        return $this->json;
    }
}