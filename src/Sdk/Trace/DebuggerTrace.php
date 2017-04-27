<?php
/**
 * Created by IntelliJ IDEA.
 * User: chuxiaofeng
 * Date: 17/4/19
 * Time: 下午10:05
 */

namespace Zan\Framework\Sdk\Trace;

use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Utilities\Encode\LZ4;

class DebuggerTrace
{
    private static $hostInfo;
    private static $reportTimeout = 5000;

    const HOST_KEY = "X-Trace-Host";
    const PORT_KEY = "X-Trace-Port";
    const ID_KEY = "X-Trace-Id";

    private $traceHost;
    private $tracePort;
    private $traceUri = "/";

    private $stack;
    private $json;

    public static function of($ctx)
    {
        if (empty($ctx)) {
            return null;
        }

        if (is_array($ctx)) {
            $ctx = array_change_key_case($ctx, CASE_LOWER);
            $k1 = strtolower(self::HOST_KEY);
            $k2 = strtolower(self::PORT_KEY);
            $k3 = strtolower(self::ID_KEY);
            if (isset($ctx[$k1]) && isset($ctx[$k2]) && $ctx[$k3]) {
                return new static($ctx[$k1],$ctx[$k2],  $ctx[$k3]);
            }
        }

        return null;
    }

    private function __construct($host, $port, $traceId)
    {
        $this->detectHostInfo();

        $this->json = self::$hostInfo;
        $this->json["trace_id"] = $traceId;
        $this->json["traces"] = [];

        $this->stack = new \SplStack();
        $this->traceHost = $host;
        $this->tracePort = $port;
    }

    public function beginTransaction($traceType, $req)
    {
        list($usec, $sec) = explode(' ', microtime());
        $begin = $sec + $usec;
        $trace = [$begin, $traceType, $req];
        $this->stack->push($trace);
    }

    public function commit($logType, $res = [])
    {
        if ($this->stack->isEmpty()) {
            return;
        }

        list($begin, $traceType, $req) = $this->stack->pop();

        list($usec, $sec) = explode(' ', microtime());
        $end = $sec + $usec;

        $info = [
            "cost" => ceil(($end - $begin) * 1000) . "ms",
            "req" => self::convert($req),
            "res" => self::convert($res),
        ];

        $this->trace($logType, $traceType, $info);
    }

    public function trace($logType, $traceType, $detail)
    {
        $this->json['traces'][] = [$logType, $traceType, $detail];
    }

    public function report()
    {
        swoole_async_dns_lookup($this->traceHost, function($host, $ip) {
            $cli = new \swoole_http_client($ip, intval($this->tracePort));
            $cli->setHeaders(["Connection" => "Closed"]);
            $timerId = swoole_timer_after(self::$reportTimeout, function() use($cli) {
                $cli->close();
            });

            $cli->post($this->traceUri, $this->json, function(\swoole_http_client $cli) use($timerId) {
                swoole_timer_clear($timerId);
                $cli->close();
            });
        });
    }

    public static function convert($var)
    {
        $var = is_array($var) ? $var : [ $var ];
        return array_map(["self", "convertHelper"], $var);
    }

    public static function convertHelper($object, $processed = [])
    {
        $type = gettype($object);
        switch ($type) {
            case "string":
                $lz4 = LZ4::getInstance();
                if ($lz4->isLZ4($object)) {
                    $object = $lz4->decode($object);
                }
                return mb_convert_encoding($object, 'UTF-8', 'UTF-8');
            case "array":
                return array_map(["self", "convertHelper"], $object);
            case "object":
                if ($object instanceof \Exception) {
                    return [
                        "class" => get_class($object),
                        "msg" => $object->getMessage(),
                    ];
                }
                $processed[] = $object;
                $kv = [ "class" => get_class($object) ];
                $reflect = new \ReflectionClass($object);
                foreach ($reflect->getProperties() as $prop) {
                    $prop->setAccessible(true);
                    $value = $prop->getValue($object);
                    if ($value === $object || in_array($value, $processed, true)) {
                        $value = '*recursion* - parent object [' . get_class($value) . ']';
                    }
                    $accessModifier = self::getAccessModifier($prop);
                    $kv[$accessModifier] = self::convertHelper($value);
                }
                return $kv;

            case "boolean":
            case "integer":
            case "double":
            case "resource":
            case "NULL":
            case "unknown type":
            default:
                return $object;
        }
    }

    private static function getAccessModifier(\ReflectionProperty $prop)
    {
        $static = $prop->isStatic() ? ' static' : '';

        if ($prop->isPublic()) {
            return 'public' . $static . ' ' . $prop->getName();
        } else if ($prop->isProtected()) {
            return 'protected' . $static . ' ' . $prop->getName();
        } else if ($prop->isPrivate()) {
            return 'private' . $static . ' ' . $prop->getName();
        } else {
            return 'unknown';
        }
    }

    private function detectHostInfo()
    {
        if (self::$hostInfo) {
            return;
        }

        /** @noinspection PhpUndefinedFunctionInspection */
        self::$hostInfo = [
            "app" => Application::getInstance()->getName(),
            "host" => gethostname(),
            "ip" => nova_get_ip(),
            "port" => Config::get("server.port"),
            "pid" => getmypid(),
        ];
    }
}