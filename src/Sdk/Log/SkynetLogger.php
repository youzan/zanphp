<?php

namespace Zan\Framework\Sdk\Log;

use Zan\Framework\Foundation\Exception\ZanException;


/**
 * Class SkynetLogger
 * @package Zan\Framework\Sdk\Log
 *
 * 日志格式规范：
 * 消息头+ “|”+ 消息体，消息头之间用“,”分割，消息体由业务自定义
 * 消息头如下结构：
 *
 * 消息头字段        类型     说明
 * appName          String	应用名
 * compress         String	body是否压缩， 0：不压缩，1压缩
 * exceptionClass   String	异常类名
 * hostname         String	主机名称
 * ipAddress        String	ip
 * level            String
 * logindex         String	日志分类
 * magic data	    String	魔鬼数字:0xsky，过滤垃圾数据
 * timeStamp        String	时间戳
 * topic            String	topic
 * version          String	版本号
 *
 */
class SkynetLogger extends SystemLogger
{
    const MAGIC_DATA = "0XSKY";

    const VERSION = "1.0";

    const TOPIC_PREFIX= "skynet";

    const DEFAULT_GROUP = "flume";

    private $version = self::VERSION;

    private $compress = 0;

    private $platform = "PHP";

    public function __construct(array $config)
    {
        $config += [
            "group" => static::DEFAULT_GROUP,
        ];

        parent::__construct($config);
        $this->priority = LOG_LOCAL3 + LOG_NOTICE;
    }

    public function format($level, $message, $context)
    {
        $header = $this->buildHeader();
        $body = $this->buildBody($level, $message, $context);
        return "{$header}{$body}\n";
    }

    private function buildHeader($level = "error")
    {
        $time = date('Y-m-d H:i:s');
        $topic = $this->buildTopic();
        return "<{$this->priority}>{$time} {$this->server} {$level}[]: $topic";
    }

    private function buildTopic()
    {
        $config = $this->config;
        $prefix = static::TOPIC_PREFIX;
        return "topic=$prefix.{$config['group']} ";
    }

    private function buildSkynetHeader($level, $exceptionClass = "")
    {
        $skynetHdr = [
            static::MAGIC_DATA,
            $this->version,
            $this->compress,
            $this->config['app'],
            $this->config['module'], // logIndex
            $this->buildTopic(),
            $this->hostname,
            $this->ip,
            intval(microtime(true) * 1000),
            $level,
            $exceptionClass,
            $this->platform,
        ];

        return implode(",", $skynetHdr);
    }

    private function buildSkynetbody($tag, $error, $extra)
    {
        return json_encode([
            "tag" => $tag,
            "error" => $error,
            "extra" => $extra,
        ]);
    }

    private function buildBody($level, $message, array $context = [])
    {
        $exceptionClass = "";
        $stackTrace = "";

        if (isset($context['exception'])) {
            $e = $context['exception'];
            if ($e instanceof \Throwable || $e instanceof \Exception) {
                $exceptionClass = get_class($e);
                $e = $context['exception'];
                $stackTrace = $this->formatException($e);
                $context['exception_metadata'] = $e instanceof ZanException ? $e->getMetadata() : [];
                unset($context['exception']);
            }
        }

        if ($context == [])
            $context = new \stdClass();

        $skynetHdr = $this->buildSkynetHeader($level, $exceptionClass);
        $skynetBody = $this->buildSkynetbody($message, $stackTrace, [$context]);

        return "$skynetHdr|$skynetBody";
    }

}
