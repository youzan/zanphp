#!/usr/bin/env php
<?php

namespace Zan\Toolkit;

/**
 * Nova 命令行工具
 * @author xiaofeng
 *
 * 2017-01-25 V1
 * 2017-04-30 加入超时参数, 加入或优化 DNS查询超时, 连接超时, 数据收发超时
 * 2017-04-30 加入attach参数
 */


if (isset($argv[1]) && $argv[1] === "install") {
    $self = __FILE__;
    `rm -rf /usr/local/bin/nova`;
    `chmod +x $self && cp $self /usr/local/bin/nova`;
    exit();
}

$usage = <<<USAGE
Usage: nova -h主机 -p端口 -m方法 -a参数 [-e附加字段 -t超时]
    -a 参数 与 -e nova协议头Attachment 均传递JSON字符串
    nova -h127.0.0.1 -p8050 -m=com.youzan.material.general.service.TokenService.getToken -a='{"xxxId":1,"scope":""}'
    nova -h127.0.0.1 -p8050 -m=com.youzan.material.general.service.TokenService.getToken -a='{"xxxId":1,"scope":""}' -e='{"xxxId":1}'
    nova -h127.0.0.1 -p8050 -m=com.youzan.material.general.service.MediaService.getMediaList -a='{"query":{"categoryId":2,"xxxId":1,"pageNo":1,"pageSize":5}}'
    nova -hqabb-dev-scrm-test0 -p8100 -mcom.youzan.scrm.customer.service.customerService.getByYzUid -a '{"xxxId":1, "yzUid": 1}'

USAGE;

$a = getopt("h:p:m:a:t:e:");
if (!isset($a['h']) || !isset($a['p']) || !isset($a['m']) || !isset($a['a'])) {
    echo "\033[1m$usage\033[0m\n";
    exit(1);
}

$args = json_decode($a['a'], true);
if ($args === null) {
    $args = [];
}
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "\033[1;31m", "JSON参数有误: ", json_last_error_msg(), "\033[0m\n";
    exit(1);
}

$attach = new \stdClass();
if (isset($a['e'])) {
    $attach = json_decode($a['e'], true);
    if ($attach === null) {
        $attach = new \stdClass();
    }
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "\033[1;31m", "-e attachment参数有误: ", json_last_error_msg(), "\033[0m\n";
        exit(1);
    }
}

$host = $a['h'];
$port = $a['p'];
$service_method = $a['m'];
$split = strrpos($service_method, ".");
if ($split === false) {
    echo "\033[1;31m", "方法错误: $service_method", "\033[0m\n";
    exit(1);
}
$service = substr($service_method, 0, $split);
$method = substr($service_method, $split + 1);


if (isset($a["t"])) {
    $timeout = min(10 * 1000, max(10, intval($a["t"])));
    NovaClient::$connectTimeout = $timeout;
    NovaClient::$sendTimeout = $timeout;
}

NovaClient::call($host, $port, $service, $method, $args, $attach, function(\swoole_client $cli, $receive, $err_msg) use ($service, $method, $args) {
    if ($cli->isConnected()) {
        $cli->close();
    }

    if ($err_msg) {
        echo "\033[1;31m$err_msg\033[0m\n";
        exit(1);
    } else {
        list($ok, $res, $attach) = $receive;
        if ($ok) {
            echo "\033[1;32m", json_encode($res, JSON_PRETTY_PRINT), "\033[0m\n";
            exit(0);
        } else {
            echo "\033[1;31m", json_encode($res, JSON_PRETTY_PRINT), "\033[0m\n";
            exit(1);
        }
    }
});


class NovaClient
{
    private static $ver_mask = 0xffff0000;
    private static $ver1 = 0x80010000;

    private static $t_call  = 1;
    private static $t_reply  = 2;
    private static $t_ex  = 3;

    public static $connectTimeout = 2000;
    public static $sendTimeout = 4000;

    private $connectTimerId;
    private $sendTimerId;
    private $seq;

    /** @var \swoole_client */
    public $client;

    private $host;
    private $port;
    private $recvArgs;
    private $callback;

    public function __construct($host, $port)
    {
        $this->host = $host;
        $this->port = $port;

        $this->client = $this->makeClient();
    }

    public static function call($host, $port, $service, $method, array $args, array $attach, callable $callback)
    {
        (new static($host, $port))->invoke($service, $method, $args, $attach, $callback);
    }

    /**
     * @param string $service
     * @param string $method
     * @param array $args
     * @param array $attach
     * @param callable $callback (receive, errorMsg)
     */
    public function invoke($service, $method, array $args, array $attach, callable $callback)
    {
        $this->recvArgs = func_get_args();
        $this->callback = $callback;

        if ($this->client->isConnected()) {
            $this->send();
        } else {
            $this->connect();
        }
    }

    private function makeClient()
    {
        $client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);

        $client->set([
            "open_length_check" => 1,
            "package_length_type" => 'N',
            "package_length_offset" => 0,
            "package_body_offset" => 0,
            "open_nova_protocol" => 1,
            "socket_buffer_size" => 1024 * 1024 * 2,
        ]);

        $client->on("error", function(\swoole_client $client) {
            $this->clearTimer();
            $cb = $this->callback;
            $cb($client, null, "ERROR: " . socket_strerror($client->errCode));
        });

        $client->on("close", function(/*\swoole_client $client*/) {
            $this->clearTimer();
        });

        $client->on("connect", function(/*\swoole_client $client*/) {
            swoole_timer_clear($this->connectTimerId);
            $this->invoke(...$this->recvArgs);
        });

        $client->on("receive", function(\swoole_client $client, $data) {
            // fwrite(STDERR, "recv: " . implode(" ", str_split(bin2hex($data), 2)) . "\n");
            swoole_timer_clear($this->sendTimerId);
            $cb = $this->callback;
            $cb($client, self::unpackResponse($data, $this->seq), null);
        });

        return $client;
    }

    private function connect()
    {
        DNS::lookup($this->host, function($ip, $host) {
            if ($ip === null) {
                $cb = $this->callback;
                $cb($this->client, null, "DNS查询超时 host:{$host}");
            } else {
                $this->connectTimerId = swoole_timer_after(self::$connectTimeout, function() {
                    $cb = $this->callback;
                    $cb($this->client, null, "连接超时 {$this->host}:{$this->port}");
                });
                assert($this->client->connect($ip, $this->port));
            }
        });
    }

    private function send()
    {
        $this->sendTimerId = swoole_timer_after(self::$sendTimeout, function() {
            $cb = $this->callback;
            $cb($this->client, null, "Nova请求超时");
        });
        $novaBin = self::packNova(...$this->recvArgs); // 多一个onRecv参数,不过没关系
        assert($this->client->send($novaBin));
    }

    /**
     * @param string $recv
     * @param int $expectSeq
     * @return array
     */
    private static function unpackResponse($recv, $expectSeq)
    {
        list($response, $attach) = self::unpackNova($recv, $expectSeq);
        $hasError = isset($response["error_response"]);
        if ($hasError) {
            $res = $response["error_response"];
        } else {
            $res = $response["response"];
        }
        return [!$hasError, $res, $attach];
    }

    /**
     * @param string $raw
     * @param int $expectSeq
     * @return array
     */
    private static function unpackNova($raw, $expectSeq)
    {
        $service = $method = $ip = $port = $seq = $attach = $thriftBin = null;
        $ok = nova_decode($raw, $service, $method, $ip, $port, $seq, $attach, $thriftBin);
        assert($ok);
        assert(intval($expectSeq) === intval($seq));

        $attach = json_decode($attach, true, 512, JSON_BIGINT_AS_STRING);

        $response = self::unpackThrift($thriftBin);
        $response = json_decode($response, true, 512, JSON_BIGINT_AS_STRING);
        assert(json_last_error() === 0);

        return [$response, $attach];
    }

    /**
     * @param string $buf
     * @return string
     */
    private static function unpackThrift($buf)
    {
        $read = function($n) use(&$offset, $buf) {
            static $offset = 0;
            assert(strlen($buf) - $offset >= $n);
            $offset += $n;
            return substr($buf, $offset - $n, $n);
        };

        $ver1 = unpack('N', $read(4))[1];
        if ($ver1 > 0x7fffffff) {
            $ver1 = 0 - (($ver1 - 1) ^ 0xffffffff);
        }
        assert($ver1 < 0);
        $ver1 = $ver1 & self::$ver_mask;
        assert($ver1 === self::$ver1);

        $type = $ver1 & 0x000000ff;
        $len = unpack('N', $read(4))[1];
        /*$name = */$read($len);
        $seq = unpack('N', $read(4))[1];
        assert($type !== self::$t_ex); // 不应该透传异常
        // invoke return string
        $fieldType = unpack('c', $read(1))[1];
        assert($fieldType === 11); // string
        $fieldId = unpack('n', $read(2))[1];
        assert($fieldId === 0);
        $len = unpack('N', $read(4))[1];
        $str = $read($len);
        $fieldType = unpack('c', $read(1))[1];
        assert($fieldType === 0); // stop

        return $str;
    }

    /**
     * @param array $args
     * @return string
     */
    private static function packArgs(array $args = [])
    {
        foreach ($args as $key => $arg) {
            if (is_object($arg) || is_array($arg)) {
                $args[$key] = json_encode($arg, JSON_BIGINT_AS_STRING, 512);
            } else {
                $args[$key] = strval($arg);
            }
        }
        return json_encode($args, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param string $service
     * @param string $method
     * @param array $args
     * @param array $attach
     * @return string
     */
    private function packNova($service, $method, array $args, array $attach)
    {
        $args = self::packArgs($args);
        $thriftBin = self::packThrift($service, $method, $args);
        $attach = json_encode($attach, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $sockInfo = $this->client->getsockname();
        $localIp = ip2long($sockInfo["host"]);
        $localPort = $sockInfo["port"];

        $return = "";
        $this->seq = nova_get_sequence();
        $ok = nova_encode("Com.Youzan.Nova.Framework.Generic.Service.GenericService", "invoke",
                $localIp, $localPort,
                $this->seq,
                $attach, $thriftBin, $return);
        assert($ok);
        return $return;
    }

    /**
     * @param string $serviceName
     * @param string $methodName
     * @param string $args
     * @param int $seq
     * @return string
     */
    private static function packThrift($serviceName, $methodName, $args, $seq = 0)
    {
        // pack \Com\Youzan\Nova\Framework\Generic\Service\GenericService::invoke
        $payload = "";

        $type = self::$t_call; // call
        $ver1 = self::$ver1 | $type;

        $payload .= pack('N', $ver1);
        $payload .= pack('N', strlen("invoke"));
        $payload .= "invoke";
        $payload .= pack('N', $seq);

        // {{{ pack args
        $fieldId = 1;
        $fieldType = 12; // struct
        $payload .= pack('c', $fieldType); // byte
        $payload .= pack('n', $fieldId); //u16

        // {{{ pack struct \Com\Youzan\Nova\Framework\Generic\Service\GenericRequest
        $fieldId = 1;
        $fieldType = 11; // string
        $payload .= pack('c', $fieldType);
        $payload .= pack('n', $fieldId);
        $payload .= pack('N', strlen($serviceName));
        $payload .= $serviceName;

        $fieldId = 2;
        $fieldType = 11;
        $payload .= pack('c', $fieldType);
        $payload .= pack('n', $fieldId);
        $payload .= pack('N', strlen($methodName));
        $payload .= $methodName;

        $fieldId = 3;
        $fieldType = 11;
        $payload .= pack('c', $fieldType);
        $payload .= pack('n', $fieldId);
        $payload .= pack('N', strlen($args));
        $payload .= $args;

        $payload .= pack('c', 0); // stop
        // pack struct end }}}

        $payload .= pack('c', 0); // stop
        // pack arg end }}}

        return $payload;
    }

    private function clearTimer()
    {
        if (swoole_timer_exists($this->connectTimerId)) {
            swoole_timer_clear($this->connectTimerId);
        }
        if (swoole_timer_exists($this->sendTimerId)) {
            swoole_timer_clear($this->sendTimerId);
        }
    }
}


/**
 * Class DNS
 * 200ms超时,重新发起新的DNS请求,重复5次
 * 无论哪个请求先收到回复立即call回调, cb 保证只会被call一次
 */
final class DNS
{
    public static $maxRetry = 5;
    public static $timeout = 200;

    public static function lookup($host, callable $cb)
    {
        self::helper($host, self::once($cb), self::$maxRetry);
    }

    private static function helper($host, callable $cb, $n)
    {
        if ($n <= 0) {
            return $cb(null, $host);
        }

        $t = swoole_timer_after(self::$timeout, function() use($host, $cb, $n) {
            self::helper($host, $cb, --$n);
        });

        return swoole_async_dns_lookup($host, function($host, $ip) use($t, $cb) {
            if (swoole_timer_exists($t)) {
                swoole_timer_clear($t);
            }
            $cb($ip, $host);
        });
    }

    private static function once(callable $fun)
    {
        $called = false;
        return function(...$args) use(&$called, $fun) {
            if ($called) {
                return;
            }
            $fun(...$args);
            $called = true;
        };
    }
}