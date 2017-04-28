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
use Zan\Framework\Network\Tcp\RpcContext;
use Zan\Framework\Utilities\Encode\LZ4;
use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Network\Http\Request\Request as HttpRequest;
use Zan\Framework\Network\Tcp\Request as TcpRequest;
use Zan\Framework\Utilities\DesignPattern\Context;

class DebuggerTrace
{
    private static $hostInfo;
    private static $reportTimeout = 5000;

    const KEY = "X-Trace-Callback";

    private $traceHost;
    private $tracePort;
    private $traceUri = "/";

    private $stack;
    private $json;

    public static function make(Request $request, Context $context)
    {
        if ($request instanceof HttpRequest) {
            $req = [
                "get" => $request->request->all(),
                "post" => $request->query->all(),
                "cookie" => $request->cookies->all(),
            ];
            $header = $req["get"] + $req["post"] + $request->headers->all();
            $name = $request->getMethod() . '-' . $request->getUrl();
            $type = Constant::HTTP;
        } else if ($request instanceof TcpRequest) {

            $req = [
                "args" => $request->getArgs(),
                "remote_ip" => $request->getRemoteIp(),
                "remote_port" => $request->getRemotePort(),
                "seq" => $request->getSeqNo(),
            ];
            $header = $request->getRpcContext()->get();
            $name = $request->getServiceName() . '.' . $request->getMethodName();
            $type = Constant::NOVA;
        } else {
            return;
        }

        $header = array_change_key_case($header, CASE_LOWER);
        $key = strtolower(self::KEY);
        if (isset($header[$key])) {
            list($host, $port, $path, $args) = self::parseKey($header[$key]);
            if ($host && $port) {
                $trace = new static($host, $port, $path, $args["id"]);

                $trace->beginTransaction($type, $name, $req);
                $context->set("debugger_trace", $trace);

                $rpcCtx = $context->get(RpcContext::KEY);
                if ($rpcCtx instanceof RpcContext) {
                    $rpcCtx->set(self::KEY, self::buildKey($host, $port, $path, $args));
                }
            }
        }
    }

    private function __construct($host, $port, $path, $traceId)
    {
        $this->detectHostInfo();

        $this->json = self::$hostInfo;
        $this->json["trace_id"] = $traceId;
        $this->json["traces"] = [];

        $this->stack = new \SplStack();
        $this->traceHost = $host;
        $this->tracePort = $port;
        $this->traceUri = $path;
    }

    public function beginTransaction($traceType, $name, $req)
    {
        list($usec, $sec) = explode(' ', microtime());
        $begin = $sec + $usec;
        $ts = date("Y-m-d H:i:s", $sec) . substr($usec, 1, 4);

        $trace = [$begin, $ts, $traceType, $name, $req];
        $this->stack->push($trace);
    }

    public function commit($logType, $res = [])
    {
        if ($this->stack->isEmpty()) {
            return;
        }

        list($begin, $ts, $traceType, $name, $req) = $this->stack->pop();

        list($usec, $sec) = explode(' ', microtime());
        $end = $sec + $usec;

        $info = [
            "ts" => $ts,
            "cost" => ceil(($end - $begin) * 1000) . "ms",
            "req" => self::convert($req),
            "res" => self::convert($res),
        ];

        $this->trace($logType, $traceType, $name, $info);
    }

    public function trace($logType, $traceType, $name, $detail)
    {
        $this->json['traces'][] = [$logType, $traceType, $name, $detail];
    }

    public function report()
    {
        swoole_async_dns_lookup($this->traceHost, function($host, $ip) {
            $cli = new \swoole_http_client($ip, intval($this->tracePort));
            $cli->setHeaders([
                "Connection" => "Closed",
                "Content-Type" => "application/json;charset=utf-8",
            ]);
            $timerId = swoole_timer_after(self::$reportTimeout, function() use($cli) {
                $cli->close();
            });

            $body = json_encode($this->json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{"err":"json_encode fail"}';
            $cli->post($this->traceUri, $body, function(\swoole_http_client $cli) use($timerId) {
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

    private static function parseKey($str)
    {
        $host = parse_url($str, PHP_URL_HOST);
        $port = parse_url($str, PHP_URL_PORT);
        $path = parse_url($str, PHP_URL_PATH) ?: "/";
        $query = parse_url($str, PHP_URL_QUERY) ?: "";
        parse_str($query, $args);

        if (empty($host) || empty($port)) {
            return [null, null, null, null];
        }

        if (!isset($args["id"])) {
            $args["id"] = TraceBuilder::generateId();
        }
        return [$host, $port, $path, $args];
    }

    private static function buildKey($host, $port, $path, $args)
    {
        return "{$host}:{$port}{$path}?" . http_build_query($args);
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