<?php
/**
 * Created by IntelliJ IDEA.
 * User: chuxiaofeng
 * Date: 17/4/19
 * Time: 下午10:05
 */

namespace Zan\Framework\Sdk\Trace;


use bar\baz\source_with_namespace;
use Zan\Framework\Foundation\Core\Debug;
use Zan\Framework\Utilities\DesignPattern\Context;

class ChromeTrace
{
    const CLASS_KEY = '___class_name';
    const TRANS_KEY = 'X-ChromeLogger-Data';

    /* trace level */
    const INFO = 'info';
    const WARN = 'warn';
    const ERROR = 'error';
    const GROUP = 'group';
    const GROUP_END = 'groupEnd';
    const GROUP_COLLAPSED = 'groupCollapsed';
    const TABLE = 'table';

    private $jsonObject;
    private $stack;

    public function __construct()
    {
        $this->jsonObject = new JSONObject();
        $this->stack = new \SplStack();
    }

    public function getJSONObject()
    {
        return $this->jsonObject;
    }

    /**
     * 发起请求
     * @param string $traceType trace类型
     * @param mixed $req 请求数据
     */
    public function beginTransaction($traceType, $req)
    {
        list($usec, $sec) = explode(' ', microtime());
        $begin = $sec + $usec;
        $trace = [$begin, $traceType, $req];
        $this->stack->push($trace);
    }

    /**
     * 与transactionBegin配对
     * @param string $logType chrome::console.${logType}
     * @param mixed $res 响应数据
     * @param JSONObject|null $remote
     */
    public function commit($logType, $res, $remote = null)
    {
        list($begin, $traceType, $req) = $this->stack->pop();

        list($usec, $sec) = explode(' ', microtime());
        $end = $sec + $usec;

        $ctx = [
            "cost" => $end - $begin,
            "req" => self::convert($req),
            "res" => self::convert($res),
        ];

        $this->jsonObject->addRow($logType, [$traceType, $ctx]);

        if ($remote instanceof JSONObject) {
            $this->jsonObject->addJSONObject($remote);
        }
    }

    /**
     * @param string $logType chrome::console.${logType}
     * @param string $traceType trace类型
     * @param mixed $args trace信息
     */
    public function trace($logType, $traceType, $args)
    {
        // $args = self::convert($args);
        $this->jsonObject->addRow($logType, [$traceType, $args]);
    }

    /**
     * @param \swoole_http_response $response
     * @return \Generator
     */
    public static function send(\swoole_http_response $response)
    {
        if (Debug::get()) {
            $self = (yield getContext('chrome_trace'));
            if ($self instanceof static) {
                $self->sendHeader($response);
            }
        }
    }

    public static function sendByCtx(\swoole_http_response $response, Context $ctx)
    {
        if (Debug::get()) {
            $self = $ctx->get('chrome_trace');
            if ($self instanceof static) {
                $self->sendHeader($response);
            }
        }
    }

    private function sendHeader(\swoole_http_response $response)
    {
        $ok = $response->header(self::TRANS_KEY, $this->jsonObject);
        if ($ok === false) {
            $jsonObj = new JSONObject();
            $jsonObj->addRow("error", ["ERROR", "maybe header value is too long"]);
            $response->header(self::TRANS_KEY, $jsonObj);
        }
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
                if (strlen($object) > 100) {
                    return substr($object, 0, 99) . "...";
                } else {
                    return $object;
                }
            case "array":
                return array_map(["self", "convertHelper"], $object);
            case "object":
                if ($object instanceof \Exception) {
                    return substr($object->getTraceAsString(), 0, 99) . "...";
                }
                $processed[] = $object;
                $kv = [ static::CLASS_KEY => get_class($object) ];
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
}