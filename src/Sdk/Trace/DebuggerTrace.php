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

    const HOST_KEY = "X-Trace-Host";
    const PORT_KEY = "X-Trace-Port";
    const ID_KEY = "X-Trace-Id";

    private $traceHost;
    private $tracePort;
    private $traceUri = "/";

    private $stack;
    private $json;

    public static function make(Request $request, Context $context)
    {
        if ($request instanceof HttpRequest) {
            $protocolHeader = $request->headers->all();
            $type = Constant::HTTP;
            $req = [
                "method" => $request->getMethod(),
                "uri" => $request->getRequestUri(),
                "get" => $request->request->all(),
                "post" => $request->query->all(),
                "cookie" => $request->cookies->all(),
            ];
        } else if ($request instanceof TcpRequest) {
            $protocolHeader = $request->getRpcContext()->get();
            $type = Constant::NOVA;
            $req = [
                "service" => $request->getGenericServiceName(),
                "method" => $request->getMethodName(),
                "args" => $request->getArgs(),
                "remote_ip" => $request->getRemoteIp(),
                "remote_port" => $request->getRemotePort(),
                "seq" => $request->getSeqNo(),
            ];
        } else {
            return;
        }

        $h = array_change_key_case($protocolHeader, CASE_LOWER);
        $k1 = strtolower(self::HOST_KEY);
        $k2 = strtolower(self::PORT_KEY);
        $k3 = strtolower(self::ID_KEY);

        if (isset($h[$k1]) && isset($h[$k2]) && $h[$k3]) {
            $host = $h[$k1];
            $port = $h[$k2];
            $id = $h[$k3];

            $trace = new static($host, $port, $id);

            $trace->beginTransaction($type, $req);
            $context->set("debugger_trace", $trace);

            $rpcCtx = $context->get(RpcContext::KEY);
            if ($rpcCtx instanceof RpcContext) {
                $rpcCtx->set(DebuggerTrace::HOST_KEY, $host);
                $rpcCtx->set(DebuggerTrace::PORT_KEY, $port);
                $rpcCtx->set(DebuggerTrace::ID_KEY, $id);
            }
        }
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