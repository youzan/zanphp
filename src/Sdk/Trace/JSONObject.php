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

final class JSONObject implements JsonSerializable
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

        "columns" => ["type", "log", "backtrace"],
        "rows"    => [],
    ];

    public function __construct()
    {
        $this->init();

        $this->json["app"] = self::$appName;
        $this->json["host"] = self::$hostName;
        $this->json["ip"] = self::$ip;
        $this->json["port"] = self::$port;
        $this->json["pid"] = self::$pid;

        self::group($this);
    }

    public static function fromRemote(array &$ctx)
    {
        $key1 = strtolower(ChromeTrace::TRANS_KEY); // swoole_header
        $key2 = ChromeTrace::TRANS_KEY;

        if (isset($ctx[$key1])) {
            $raw = $ctx[$key1];
            unset($ctx[$key1]);
        } else if (isset($ctx[$key2])) {
            $raw = $ctx[$key2];
            unset($ctx[$key2]);
        } else {
            return null;
        }

        if ($raw && $json = self::decode($raw)) {
            $self = new self();
            $self->json = $json + $self->json;
            return $self;
        } else {
            return null;
        }
    }

    public function addRow($logType, array $logs, JSONObject $trace = null, $backtrace = null)
    {
        $this->json['rows'][] = [$logType, $logs, $trace, $backtrace];
    }

    public function addJSONObject(JSONObject $remote)
    {
        self::group($remote);
        $this->json["rows"] = array_merge($this->json["rows"], $remote->json["rows"]);
        self::groupEnd($remote);
    }

    public function __toString()
    {
        return $this->encode();
    }

    public function jsonSerialize()
    {
        self::groupEnd($this);
        return $this->json;
    }

    private static function group(JSONObject $self)
    {
        $trace = $self->json;
        $title = "{$trace["app"]}  [host={$trace["host"]}, ip={$trace["ip"]}, port={$trace["port"]}, pid={$trace["pid"]}]";
        $self->json["rows"][] = ["group", [$title], null, null];
    }

    private static function groupEnd(JSONObject $self)
    {
        $self->json["rows"][] = ["groupEnd", [], null, null];
    }

    private function encode()
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

    private static function decode($raw)
    {
        return json_decode(base64_decode($raw), true);

        $lz4Str = base64_decode($raw);
        if ($lz4Str !== false) {
            $jsonStr = LZ4::getInstance()->decode($lz4Str);
            if ($jsonStr !== false) {
                $json = json_decode($jsonStr, true);
                if (isset($json["columns"]) && isset($json["rows"])) {
                    return $json;
                }
            }
        }
        sys_error("ChromeTrace decode fail, raw=" . strval($raw));
        return [];
    }

    private function init()
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
}