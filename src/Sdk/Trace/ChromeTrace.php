<?php
/**
 * Created by IntelliJ IDEA.
 * User: chuxiaofeng
 * Date: 17/4/19
 * Time: 下午10:05
 */

namespace Zan\Framework\Sdk\Trace;


use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Core\Debug;
use Zan\Framework\Utilities\DesignPattern\Context;

class ChromeTrace
{
    const CLASS_KEY = '___class_name';
    const TRANS_KEY = 'X-ChromeLogger-Data';

    const INFO = 'info';
    const WARN = 'warn';
    const ERROR = 'error';
    const GROUP = 'group';
    const GROUP_END = 'groupEnd';
    const GROUP_COLLAPSED = 'groupCollapsed';
    const TABLE = 'table';

    private static $appName;
    private static $hostName;
    private static $ip;
    private static $pid;

    private $jsonObject;
    private $stack;

    public static function getInstance()
    {
        yield getContext("chrome_trace");
    }

    public function __construct()
    {
        if (!Debug::get()) {
            return;
        }
        $this->init();
        $this->jsonObject = new ChromeTraceJSONObject();
        $this->stack = new \SplStack();
        $this->trace(static::INFO, self::$appName, [
            "app" => self::$appName,
            "host" => self::$hostName,
            "ip" => self::$ip,
        ]);
    }

    private function init()
    {
        if (self::$appName) {
            return;
        }

        self::$appName = Application::getInstance()->getName();
        self::$hostName = gethostname();
        self::$ip = nova_get_ip();
        self::$pid = getmypid();
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
     * 与transactionBegin配对, 成功 level = "info", 失败 level = "error"
     * @param string $level chrome::console.{level}
     * @param mixed $res 响应数据
     */
    public function commit($level, $res)
    {
        list($begin, $traceType, $req) = $this->stack->pop();

        list($usec, $sec) = explode(' ', microtime());
        $end = $sec + $usec;

        $trace = [
            "req" => self::convert($req),
            "res" => self::convert($res),
            "cost" => $end - $begin
        ];

        $this->jsonObject->addRow($level, [$traceType, $trace]);
    }

    /**
     * @param string $level chrome::console.{level}
     * @param string $traceType trace类型
     * @param mixed $args trace信息
     */
    public function trace($level, $traceType, $args)
    {
        $logs = self::convert($args);
        $this->jsonObject->addRow($level, [$traceType, $logs]);
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
                $response->header(self::TRANS_KEY, $self->buildTrace());
            }
        }
    }

    public static function sendByCtx(\swoole_http_response $response, Context $ctx)
    {
        if (Debug::get()) {
            $self = $ctx->get('chrome_trace');
            if ($self instanceof static) {
                $response->header(self::TRANS_KEY, $self->buildTrace());
            }
        }
    }

    public static function getByCtx(Context $ctx)
    {
        if (Debug::get()) {
            $self = $ctx->get('chrome_trace');
            if ($self instanceof static) {
            }
        }
        return [];
    }


    private function buildTrace()
    {
        return base64_encode(json_encode($this->jsonObject, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private static function convert($var)
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