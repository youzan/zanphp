<?php
/**
 * Created by IntelliJ IDEA.
 * User: chuxiaofeng
 * Date: 17/4/19
 * Time: 下午10:05
 */

namespace Zan\Framework\Sdk\Trace;


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

    public static function getInstance()
    {
        yield getContext("chrome_trace");
    }

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
     * @param array|null $remoteCtx 远程trace数据 httpHeader or novaAttachment
     */
    public function commit($logType, $res, array &$remoteCtx = null)
    {
        list($begin, $traceType, $req) = $this->stack->pop();

        list($usec, $sec) = explode(' ', microtime());
        $end = $sec + $usec;

        $ctx = [
            "time" => $begin,
            "cost" => $end - $begin,
            "req" => self::convert($req),
            "res" => self::convert($res),
        ];

        $this->jsonObject->addRow($logType, [$traceType, $ctx]);

        $remoteCtx = JSONObject::fromRemote($remoteCtx);
        if ($remoteCtx) {
            $this->jsonObject->addJSONObject($remoteCtx);
        }
    }

    /**
     * @param string $logType chrome::console.${logType}
     * @param string $traceType trace类型
     * @param mixed $args trace信息
     */
    public function trace($logType, $traceType, $args)
    {
        $args = self::convert($args);
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
        return array_map(["self", "objectConvert"], $var);
    }

    public static function objectConvert($object, $processed = [])
    {
        if (!is_object($object)) {
            return $object;
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
            $kv[$accessModifier] = self::objectConvert($value);
        }

        return $kv;
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