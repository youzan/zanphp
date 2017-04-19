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

    private $jsonObject;

    public function __construct()
    {
        if (!Debug::get()) {
            return;
        }

        $this->jsonObject = new ChromeTraceJSONObject();

        $this->init();
        $this->log(static::INFO, [
            'app' => self::$appName,
            'host' => self::$hostName,
            'ip' => self::$ip,
        ]);
    }

    public function getJSONObject()
    {
        return $this->jsonObject;
    }

    public function log($type, array $args)
    {
        if (count($args) === 0 && $type != static::GROUP_END) {
            return;
        }

        $logs = [];
        foreach ($args as $arg) {
            $logs[] = $this->convert($arg);
        }

        $this->jsonObject->addRow($type, $logs);
    }

    /**
     * @param $type
     * @param array $args
     * @return \Generator
     */
    public static function trace($type, array $args)
    {
        if (Debug::get()) {
            $self = (yield getContext('chrome_trace'));
            if ($self instanceof static) {
                $self->log($type, $args);
            }
        }
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

    public function tcpTrace()
    {

    }

    private function init()
    {
        if (self::$appName) {
            return;
        }

        self::$appName = Application::getInstance()->getName();
        self::$hostName = gethostname();
        self::$ip = nova_get_ip();
    }

    private function convert($object, $processed = [])
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
            $kv[$accessModifier] = $this->convert($value);
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

    private function buildTrace()
    {
        return base64_encode(json_encode($this->jsonObject, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}