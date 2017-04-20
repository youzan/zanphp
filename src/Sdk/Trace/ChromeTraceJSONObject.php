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
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Utilities\Encode\LZ4;

class ChromeTraceJSONObject implements JsonSerializable
{
    private static $appName;
    private static $hostName;
    private static $ip;
    private static $port;
    private static $pid;

    private $json = [
        "app" => "",
        "host" => "",
        "ip" => "",
        "pid" => "",
        /**
         * type: chrome::console.{type}
         * log: 日志信息
         * backtrace:
         * trace: 远程信息
         */
        "columns" => ["type", "log", "trace", "backtrace"],
        "rows"    => [],
    ];

    public function __construct($json = null)
    {
        $this->initEnv();
        if ($json) {
            $this->json = $json;
        } else {
            $this->json["app"] = self::$appName;
            $this->json["host"] = self::$hostName;
            $this->json["ip"] = self::$ip;
            $this->json["port"] = self::$port;
            $this->json["pid"] = self::$pid;
        }
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
                // TODO +default
                if (isset($json["columns"]) && isset($json["rows"])) {
                    return new self($json);
                }
            }
        }
        sys_error("ChromeTrace decode fail, raw=" . strval($raw));
        return new self();
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
        self::$port = Config::get("server.port");
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