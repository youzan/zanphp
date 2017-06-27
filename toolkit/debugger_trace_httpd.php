#!/usr/bin/env php
<?php

namespace ZanPHP\Toolkit;


/**
 * Zan Debugger Trace
 * @author xiaofeng
 *
 * TODO 1. 命令行版本
 * TODO 2. 历史记录
 *
 */

$port = isset($argv[1]) ? intval($argv[1]) : 7777;

$serv = new TraceServer($port);
$serv->start();

class TraceServer
{
    const RFC6455GUID = "258EAFA5-E914-47DA-95CA-C5AB0DC85B11";
    const KEY = "X-Trace-Callback";

    public $localIp;
    public $port;

    /**
     * @var \swoole_http_server
     */
    public $traceServer;

    public $fds = [];

    public function __construct($port = 7777)
    {
        $this->localIp = gethostbyname(gethostname());
        $this->port = $port;
        $this->traceServer = new \swoole_websocket_server("0.0.0.0", $port, SWOOLE_BASE);

        $this->traceServer->set([
            // 'log_file' => __DIR__ . '/trace.log',
            // 'buffer_output_size' => 1024 * 1024 * 1024,
            // 'pipe_buffer_size' => 1024 * 1024 * 1024,
            // 'max_connection' => 100,
            // 'max_request' => 100000,
            'open_tcp_nodelay' => 1,
            'open_cpu_affinity' => 1,
            'daemonize' => 0,
            'reactor_num' => 1,

            // 因为要把http返回trace信息的sid与websocket连接关联起来,
            // 多个worker需要共享数据, 这里简单处理成一个worker
            'worker_num' => 1,
            'dispatch_mode' => 3, // TODO -> bind

        ]);
    }

    public function start()
    {
        $this->traceServer->on('start', [$this, 'onStart']);
        $this->traceServer->on('shutdown', [$this, 'onShutdown']);

        $this->traceServer->on('workerStart', [$this, 'onWorkerStart']);
        $this->traceServer->on('workerStop', [$this, 'onWorkerStop']);
        $this->traceServer->on('workerError', [$this, 'onWorkerError']);

        $this->traceServer->on('connect', [$this, 'onConnect']);
        $this->traceServer->on('request', [$this, 'onRequest']);

        $this->traceServer->on('open', [$this, 'onOpen']);
        $this->traceServer->on('handshake', [$this, 'onHandshake']);
        $this->traceServer->on('message', [$this, 'onMessage']);

        $this->traceServer->on('close', [$this, 'onClose']);

        sys_echo("server starting {$this->localIp}:{$this->port}");
        $this->traceServer->start();
    }

    public function onStart(\swoole_websocket_server $server)
    {
        sys_echo("server starting ......");
    }

    public function onShutdown(\swoole_websocket_server $server)
    {
        sys_echo("server shutdown .....");
    }

    public function onConnect()
    {

    }

    public function onWorkerStart(\swoole_websocket_server $server, $workerId)
    {
        $_SERVER["WORKER_ID"] = $workerId;
        sys_echo("worker #$workerId starting .....");
        $this->startHeartbeat();
    }

    public function onWorkerStop(\swoole_websocket_server $server, $workerId)
    {

    }

    public function onWorkerError(\swoole_websocket_server $server, $workerId, $workerPid, $exitCode, $sigNo)
    {
        sys_echo("worker error happening [workerId=$workerId, workerPid=$workerPid, exitCode=$exitCode, signalNo=$sigNo]...");
    }

    public function onHandshake(\swoole_http_request $request, \swoole_http_response $response)
    {
        $wsSecKey = $this->checkAndCalcWebSocketKey($request->header);

        if ($wsSecKey === false) {
            $response->status(400);
            $response->end();
            return false;
        }

        // @from https://zh.wikipedia.org/wiki/WebSocket
        // Sec-WebSocket-Version 表示支持的Websocket版本。RFC6455要求使用的版本是13，之前草案的版本均应当被弃用
        $headers = [
            'Upgrade'               => 'websocket',
            'Connection'            => 'Upgrade',
            'Sec-WebSocket-Accept'  => $wsSecKey,
            'Sec-WebSocket-Version' => '13',
            'KeepAlive'             => 'off',
        ];

        foreach ($headers as $key => $val) {
            $response->header($key, $val);
        }

        // 设置ws连接的sid, 必须保证sw与http同域, 共享cookie中sid传递到request中
        // trace信息携带该sid, 根据该sid将trace信息推送到正确的ws连接
        $response->cookie("sid", $request->fd);
        $response->header("X-Trace-Sid", $request->fd);

        // 101: switching protocols
        $response->status(101);
        $response->end();

        $this->fds[$request->fd] = true;
        return true;
    }

    public function onClose(\swoole_websocket_server $server, $fd)
    {
        unset($this->fds[$fd]);
    }

    public function onOpen(\swoole_websocket_server $server, \swoole_http_request $request)
    {
        sys_echo("handshake success with fd#{$request->fd}");
    }

    public function onMessage(\swoole_websocket_server $server, \swoole_websocket_frame $frame)
    {
        // 这里需要处理粘包
        /*
        if ($frame->data == "close") {
            $server->close($frame->fd);
            unset($this->fds[$frame->fd]);
        } else {
            sys_echo("receive from {$frame->fd}:{$frame->data}, opcode:{$frame->opcode}, finish:{$frame->finish}");
        }
        */
    }

    // block
    private function getHostByAddr($addr)
    {
        static $cache = [];
        if (!isset($cache[$addr])) {
            $cache[$addr] = gethostbyaddr($addr) ?: $addr;
        }

        return $cache[$addr];
    }

    public function onRequest(\swoole_http_request $request, \swoole_http_response $response)
    {
        $server = $request->server;
        $uri = $server["request_uri"];
        $method = $server["request_method"];


        $remoteAddr = $server["remote_addr"];
        $remotePort = $server["remote_port"];
        $remoteHost = $this->getHostByAddr($remoteAddr);
        sys_echo("$method $uri [$remoteHost:$remotePort]");

        if ($uri === "/favicon.ico") {
            $response->status(404);
            $response->end();
            return;
        }

        if ($uri === "/report") {
            if (isset($request->get["sid"])) {
                $fd = $request->get["sid"];
                if ($this->traceServer->exist($fd)) {
                    if (isset($this->fds[$fd])) {

                        // 响应上报trace信息请求, 并通过web socket连接中转
                        $body = $request->rawcontent();
                        $response->status(200);
                        $response->end();

                        $this->pushTrace($fd, $body);
                        return;
                    }
                } else {
                    unset($this->fds[$fd]);
                }
            }

            $response->status(404);
            $response->end();
            return;
        }

        if ($uri === "/request") {
            $get = $request->get;
            $body = $request->rawcontent();
            $data = json_parse($body, $errmsg) ?: [];
            if ($errmsg) {
                $response->status(200);
                $response->end("{\"error\":\"json parser error $errmsg\"}");
                return;
            }

            if (isset($get["sid"]) && isset($data["protocol"]) && isset($data["uri"])) {
                $proto = $data["protocol"];
                $sid = $get["sid"];
                if ($proto === "http") {
                    $this->httpRequest($sid, $data, $response);
                    return;
                } else if ($proto === "nova") {
                    $this->novaRequest($sid, $data, $response);
                    return;
                }
            }

            $response->status(200);
            $response->end('{"error":"invalid args"}');
            return;
        }

        $this->index($response);
    }

    private function novaRequest($sid, array $data, \swoole_http_response $response)
    {
        $uri = $data["uri"];
        $host = parse_url($uri, PHP_URL_HOST);
        $port = parse_url($uri, PHP_URL_PORT);

        if (!$host || !$port) {
            $response->status(200);
            $response->end("{\"error\":\"invalid service uri\"}");
            return;
        }

        $serviceMethod = ltrim(parse_url($uri, PHP_URL_PATH), "/");
        if (empty($serviceMethod)) {
            $response->status(200);
            $response->end("{\"error\":\"invalid service: $serviceMethod\"}");
            return;
        }

        // service & method
        $split = strrpos($serviceMethod, ".");
        if ($split === false) {
            $response->status(200);
            $response->end("{\"error\":\"invalid service $serviceMethod\"}");
            return;
        }
        $service = substr($serviceMethod, 0, $split);
        $method = substr($serviceMethod, $split + 1);

        $args = json_parse(array_get($data, "args", "{}"), $errmsg1) ?: [];
        $attach = json_parse(array_get($data, "attach", "{}"), $errmsg2) ?: [];
        unset($args[""]);
        unset($attach[""]);

        if ($errmsg1 || $errmsg2) {
            $response->status(200);
            $response->end("{\"error\":\"json parser error $errmsg1 $errmsg2\"}");
            return;
        }

        $attach[self::KEY] = $this->getCallbackUrl($sid);

        NovaClient::call($host, $port, $service, $method, $args, $attach, function(\swoole_client $cli, $resp, $errMsg) use($response) {
            if ($cli->isConnected()) {
                $cli->close();
            }
            if ($errMsg) {
                $response->status(200);
                $response->end("{\"error\":\"nova call: $errMsg\"}");
                return;
            } else {
                list($ok, $res, $attach) = $resp;
                $response->status(200);
                $response->end(json_encode([
                    "ok" => $ok,
                    "nova_result" => $res,
                ]));
            }
            return;

        });
        return;
    }

    private function httpRequest($sid, array $data, \swoole_http_response $response)
    {
        $method = array_get($data, "method", "GET");
        $headers = json_parse(array_get($data, "header", "{}"), $errmsg1) ?: [];
        $cookies = json_parse(array_get($data, "cookie", "{}"), $errmsg2) ?: [];
        unset($headers[""]);
        unset($cookies[""]);
        $body = array_get($data, "body", "");

        if ($errmsg1 || $errmsg2) {
            $response->status(200);
            $response->end("{\"error\":\"json parser error $errmsg1 $errmsg2\"}");
            return;
        }

        $uri = $data["uri"];
        $host = parse_url($uri, PHP_URL_HOST);
        $port  = parse_url($uri, PHP_URL_PORT);

        if (!$host || !$port) {
            $response->status(200);
            $response->end("{\"error\":\"invalid service uri\"}");
            return;
        }

        $query = parse_url($uri, PHP_URL_QUERY);
        $path = parse_url($uri, PHP_URL_PATH) . "?" . $query;

        $headers[self::KEY] = $this->getCallbackUrl($sid);

        DNS::lookup($host, function($ip)
            use($path, $port, $method, $query, $headers, $cookies, $body, $response) {
            if ($ip === null) {
                $response->status(200);
                $response->end('{"error":"dns lookup timeout"}');
                return;
            }

            $cli = new \swoole_http_client($ip, intval($port));
            $timeout = 3000;
            $timerId = swoole_timer_after($timeout, function() use($cli, $response) {
                $response->status(200);
                $response->end('{"error":"http request timeout"}');
                if ($cli->isConnected()) {
                    $cli->close();
                }
            });
            $cli->setMethod($method);
            $cli->setHeaders([
                    "Connection" => "Closed",
                    "Content-Type" => "application/json;charset=utf-8",
                ] + $headers);
            $cli->setCookies($cookies);
            $cli->setData($body);
            $cli->execute($path, function(\swoole_http_client $cli) use($timerId, $response) {
                swoole_timer_clear($timerId);
                $response->status(200);
                $response->end($cli->body);
                $cli->close();
            });
        });
    }

    // TODO: 配置地址
    private function getCallbackUrl($sid)
    {
        // server.ngrok.cc:55674
        // return "http://47.90.92.56:55674/report?sid=$sid";
        return "http://{$this->localIp}:{$this->port}/report?sid=$sid";
    }

    /**
     * @param $fd
     * @param $data
     * WEBSOCKET_OPCODE_CONTINUATION_FRAME = 0x0,
     * WEBSOCKET_OPCODE
     * _TEXT_FRAME = 0x1,
     * WEBSOCKET_OPCODE_BINARY_FRAME = 0x2,
     * WEBSOCKET_OPCODE_CONNECTION_CLOSE = 0x8,
     * WEBSOCKET_OPCODE_PING = 0x9,
     * WEBSOCKET_OPCODE_PONG = 0xa,
     */
    private function pushTrace($fd, $data)
    {
        $payload = $this->pack($data);
        $this->traceServer->push($fd, $payload, 0x2, true);
    }

    private function startHeartbeat()
    {
        swoole_timer_tick(5000, function() {
            foreach ($this->fds as $fd => $_) {
                $payload = $this->pack("PING");
                $this->traceServer->push($fd, $payload, 0x2);
            }
        });
    }

    /**
     * Sec-WebSocket-Key是随机的字符串，服务器端会用这些数据来构造出一个SHA-1的信息摘要。
     * 把“Sec-WebSocket-Key”加上一个特殊字符串“258EAFA5-E914-47DA-95CA-C5AB0DC85B11”，
     * 然后计算SHA-1摘要，之后进行BASE-64编码，将结果做为“Sec-WebSocket-Accept”头的值，返回给客户端。
     * 如此操作，可以尽量避免普通HTTP请求被误认为Websocket协议。
     * @from https://zh.wikipedia.org/wiki/WebSocket
     *
     * @param array $header
     * @return bool|string
     */
    private function checkAndCalcWebSocketKey(array $header)
    {
        if (isset($header['sec-websocket-key']))  {
            $wsSecKey = $header['sec-websocket-key'];

            // base64 RFC http://www.ietf.org/rfc/rfc4648.txt
            // http://stackoverflow.com/questions/475074/regex-to-parse-or-validate-base64-data
            // ^(?:[A-Za-z0-9+/]{4})*(?:[A-Za-z0-9+/]{2}==|[A-Za-z0-9+/]{3}=)?$

            // The number of '=' signs at the end of a base64 value must not exceed 2
            // In base64, if the value ends with '=' then the last character must be one of [AEIMQUYcgkosw048]
            // In base64, if the value ends with '==' then the last character must be one of [AQgw]

            $isValidBase64 = preg_match('#^[+/0-9A-Za-z]{21}[AQgw]==$#', $wsSecKey) != 0;
            $isValidLen = strlen(base64_decode($wsSecKey)) === 16;

            if ($isValidBase64 && $isValidLen) {
                return base64_encode(sha1($wsSecKey . self::RFC6455GUID, true));
            }
        }

        return false;
    }

    private function pack($pushData)
    {
        return pack("N", strlen($pushData)) . $pushData;
    }

    private function index(\swoole_http_response $response)
    {
        $response->status(200);
        $response->end(<<<'HTML'
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>Debugger Trace</title>
  <link rel=icon href=https://b.yzcdn.cn/v2/image/yz_fc.ico />
  <link href="http://apps.bdimg.com/libs/highlight.js/9.1.0/styles/monokai-sublime.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Droid+Sans+Mono|Roboto|Open+Sans|Source+Code+Pro" rel="stylesheet">
  <!--font-family: 'Droid Sans Mono', monospace;
  font-family: 'Source Code Pro', monospace;
  font-family: 'Roboto', sans-serif;
  font-family: 'Open Sans', sans-serif;-->


</head>
<style>
  /*::-webkit-scrollbar {
    height: 12px;
    width: 12px;
    overflow: visible;
  }
  
   ::-webkit-scrollbar-track {
    background-color: #F7F6F6;
    background-clip: padding-box;
    border: solid transparent;
    border-width: 3px;
    border-radius: 100px;
  }
  
   ::-webkit-scrollbar-button {
    height: 0;
    width: 0;
  }
  
   ::-webkit-scrollbar-thumb {
    border-radius: 100px;
    background-clip: padding-box;
    border: solid transparent;
    border-width: 3px;
  }
  
   ::-webkit-scrollbar-corner {
    background: transparent;
  }
  
   ::-webkit-scrollbar-thumb {
    background-color: #E2E2E2;
  }*/
  /***********************************************************/
  
   ::-webkit-scrollbar {
    height: 14px;
    width: 14px;
  }
  
   ::-webkit-scrollbar-track {
    background: #150010;
  }
  
   ::-webkit-scrollbar-thumb {
    background: #302;
  }
  
   ::-webkit-scrollbar-thumb:window-inactive {
    background: #302;
  }
  
   ::-webkit-scrollbar-corner {
    background: #201;
  }
  
  .wide-input {
    background: #444;
    color: #ececec;
    border-radius: 3px;
    border: 0;
    /*border: 1px solid #666;*/
    overflow: auto;
    padding-left: 10px;
    padding-right: 10px;
    width: calc(100% - 30px);
    height: 25px;
  }
  
  .label {
    width: 70px;
    margin: 0 10px 0 10px;
  }
  
  .btn {
    box-sizing: border-box;
    border-radius: 3px;
    height: 30px;
    padding: 0 10px 0 10px;
    display: inline-flex;
    flex-direction: row;
    justify-content: center;
    align-items: center;
    text-align: center;
    font-size: 15px;
    font-weight: normal;
    font-family: 'Droid Sans Mono', monospace, Helvetica, Arial, sans-serif;
    color: #fff;
    -webkit-user-select: none;
    user-select: none;
    cursor: pointer;
  }
  
  .btn-secondary {
    background-color: #F0F0F0;
    color: #808080;
    min-width: 100px;
  }
  
  .btn-secondary:hover,
  .btn-secondary.is-hovered {
    background-color: #DCDCDC;
    color: #808080;
  }
  
  input,
  button {
    font-size: 15px;
  }
  
  code {
    font-family: 'Droid Sans Mono', monospace;
    font-size: 12px;
  }
  /***********************************************************/
  
  body {
    /*background: #333;*/
    background: #333;
    color: white;
    font-size: 10pt;
    margin: 0;
    width: 100vw;
    height: 100vh;
    overflow: hidden;
    position: absolute;
    font-family: 'Droid Sans Mono', monospace, Helvetica, Arial, sans-serif;
  }
  
  .app-root {
    width: 100%;
    height: 100%;
    overflow: hidden;
    position: absolute;
    /**/
    overflow-x: auto;
  }
  
  .app-requester {
    display: flex;
    flex-direction: column;
    min-width: 900px;
  }
  
  body,
  .app-root,
  .app-requester {
    position: absolute;
    height: 100%;
    width: 100%;
  }
  
  .app-requester .requester-header {
    flex: 0 0 50px;
  }
  
  .requester-header {
    background-color: #464646;
    z-index: 90;
    display: flex;
    flex-direction: row;
    box-shadow: 0 1px 4px 0 rgba(0, 0, 0, 0.37);
  }
  
  .app-requester .requester-contents {
    flex: 1;
  }
  
  .requester-contents {
    position: relative;
    display: flex;
    flex-direction: row;
    overflow: hidden;
  }
  
  .requester-left-sidebar-wrapper {
    flex: 0 0 auto;
    z-index: 20;
    display: flex;
    flex-direction: column;
    position: relative;
    width: 200px;
  }
  
  .requester-left-sidebar-resize-handle {
    position: absolute;
    top: 0;
    bottom: 0;
    right: -5px;
    width: 10px;
    z-index: 100;
    cursor: ew-resize;
  }
  
  .requester-left-sidebar {
    /*background-color: #FAFAFA;*/
    z-index: 20;
    border-right: 1px solid #DBDBDB;
    box-sizing: border-box;
    box-shadow: 1px 0 4px 0 rgba(0, 0, 0, 0.2);
    display: flex;
    flex-direction: column;
    flex: 1;
    overflow-y: hidden;
  }
  /***********************************************************/
  
  .requester-content-builder {
    display: flex;
    flex-direction: row;
  }
  
  .requester-content {
    flex: 1;
    overflow: hidden;
  }
  
  .requester-content-builder .requester-builder {
    flex: 1;
    display: flex;
    flex-direction: column;
  }
  
  .requester-builder {
    display: flex;
    flex-direction: column;
    overflow-x: auto;
  }
  
  .requester-builder-header {
    flex: 0 0 50px;
    display: flex;
    flex-direction: row;
    /*border-bottom: 1px solid #DBDBDB;*/
    border-bottom: 14px solid #302;
  }
  
  .requester-builder-header.connected {
    flex: 0 0 135px;
  }
  
  .requester-builder-contents {
    background: inherit;
    flex: 1;
    overflow-y: hidden;
    display: flex;
    flex-direction: column;
  }
  
  .requester-tab-contents {
    flex: 1;
    display: flex;
    flex-direction: row;
    overflow: hidden;
  }
  
  .layout-1-column.requester-tab-content {
    overflow-y: scroll;
  }
  
  .requester-tab-content {
    flex: 1;
    display: flex;
    flex-direction: column;
  }
  
  .requester-tab-content.is-hidden {
    display: none;
  }
  
  .requester-contents .is-hidden {
    display: none;
  }
  
  .is-hidden {
    display: none;
  }
  /***********************************************************/
  
  .hide-when-not-connected {
    width: 100%;
    display: none;
  }
  
  .connected .hide-when-connected {
    display: none;
  }
  
  .connected .hide-when-not-connected {
    display: block;
  }
  /*.connected .hide-when-not-connected p { color: #33ff44 }*/
  
  #connectServer {
    background-color: #a0d0d1;
    background-image: -webkit-linear-gradient(top, #72d1ca, #a0d0d1);
    border: 1px solid transparent;
    color: white;
    border-radius: 2px;
  }
  
  #connectServer:hover {
    background-color: #b8d0d1;
    background-image: -webkit-linear-gradient(top, #abd1d0, #b8d0d1);
    border: 1px solid #b8d0d1;
  }
  
  #disconnectServer {
    background-color: #D14836;
    background-image: -webkit-linear-gradient(top, #DD4B39, #D14836);
    border: 1px solid transparent;
    color: white;
    border-radius: 2px;
  }
  
  #disconnectServer:hover {
    background-color: #C53727;
    background-image: -webkit-linear-gradient(top, #DD4B39, #C53727);
    border: 1px solid #B0281A;
  }
  
  #logClear {
    background-color: #a0d0d1;
    background-image: -webkit-linear-gradient(top, #72d1ca, #a0d0d1);
    border: 1px solid transparent;
    color: white;
    border-radius: 2px;
  }
  
  #logClear:hover {
    background-color: #b8d0d1;
    background-image: -webkit-linear-gradient(top, #abd1d0, #b8d0d1);
    border: 1px solid #b8d0d1;
  }
  
  #doSend {
    background-color: #a0d0d1;
    background-image: -webkit-linear-gradient(top, #72d1ca, #a0d0d1);
    border: 1px solid transparent;
    color: white;
    border-radius: 2px;
  }
  
  #doSend:hover {
    background-color: #b8d0d1;
    background-image: -webkit-linear-gradient(top, #abd1d0, #b8d0d1);
    border: 1px solid #b8d0d1;
  }
  
  #doSend:disabled {
    background-color: #797979;
    background-image: -webkit-linear-gradient(top, #444, #797979);
    border: 1px solid #797979;
  }
  
  #doSend:disabled:hover {
    background-color: #797979;
    background-image: -webkit-linear-gradient(top, #444, #797979);
    border: 1px solid #797979;
  }
  
  .request-line {
    display: flex;
    flex-flow: row;
    justify-content: space-between;
    align-items: center;
  }
  
  .request-line .request-protocol {
    width: 90px;
  }
  
  #protocol-select {
    height: 25px;
    width: 70px;
    margin-left: 10px;
    font-size: 16px;
  }
  
  .request-line .request-url {
    flex: 1;
  }
  
  .request-line .request-act {
    width: 200px;
  }
  
  .request-line .request-act>button {
    width: 60px;
  }
  
  .label-input {
    flex: 1;
  }
  
  .protocol-none .nova-is-hide,
  .protocol-none .http-is-hide,
  .protocol-http .nova-is-hide,
  .protocol-nova .http-is-hide {
    display: none;
  }
</style>

<body>
  <div class="app-root">
    <div class="app-requester">
      <!--<div class="requester-header">-->
      <!--<h1>Zan Debugger Trace</h1>-->
      <!--</div>-->

      <div class="requester-contents">
        <!--<div class="requester-left-sidebar-wrapper">-->
        <!--<div class="requester-left-sidebar"></div>-->
        <!--<div class="requester-left-sidebar-resize-handle"></div>-->
        <!--</div>-->

        <div class="requester-content requester-content-builder">
          <div class="requester-builder">

            <div class="requester-builder-header">
              <div class="hide-when-connected">
                <p><button id="connectServer" class="btn">Connect</button></p>
              </div>

              <div class="hide-when-not-connected">
                <p>
                  <div class="request-line">
                    <div class="request-protocol">
                      <select name="protocol" id="protocol-select">
                        <option value="http">HTTP</option>
                        <option value="nova">NOVA</option>
                      </select>
                    </div>
                    <div class="request-url"><input class="wide-input" type="text" id="url" value=""></div>
                    <div class="request-act">
                      <button id="doSend" class="btn">Send</button>
                      <button id="logClear" class="btn">Clear</button>
                      <button id="disconnectServer" class="btn">Stop</button>
                    </div>
                  </div>
                </p>

                <div id="request-args" class="protocol-none">
                  <p>
                    <div class="request-line nova-is-hide">
                      <span class="label">ArgsJson</span>
                      <div class="label-input"><input class="wide-input" type="text" id="nova-args" value="{&quot;&quot;:&quot;&quot;}"></div>
                    </div>
                  </p>
                  <p>
                    <div class="request-line nova-is-hide">
                      <span class="label">Attach</span>
                      <div class="label-input"><input class="wide-input" type="text" id="nova-attach" value="{&quot;&quot;:&quot;&quot;}"></div>
                    </div>
                  </p>

                  <p>
                    <div class="request-line http-is-hide">
                      <span class="label">Method</span>
                      <select name="" id="http-method">
                        <option value="GET">GET</option>
                        <option value="POST">POST</option>
                        <option value="PUT">PUT</option>
                        <option value="PATCH">PATCH</option>
                        <option value="DELETE">DELETE</option>
                        <option value="COPY">COPY</option>
                        <option value="HEAD">HEAD</option>
                        <option value="OPTIONS">OPTIONS</option>
                        <option value="LINK">LINK</option>
                        <option value="UNLINK">UNLINK</option>
                        <option value="PURGE">PURGE</option>
                        <option value="LOCK">LOCK</option>
                        <option value="UNLOCK">UNLOCK</option>
                        <option value="PROPFIND">PROPFIND</option>
                        <option value="VIEW">VIEW</option>
                      </select>
                      <span class="label">Header</span>
                      <div class="label-input"><input class="wide-input" type="text" id="http-header" value="{&quot;&quot;:&quot;&quot;}"></div>
                      <span class="label">Cookie</span>
                      <div class="label-input"><input class="wide-input" type="text" id="http-cookie" value="{&quot;&quot;:&quot;&quot;}"></div>
                    </div>
                  </p>
                  <p>
                    <div class="request-line http-is-hide">
                      <span class="label">Body</span>
                      <div class="label-input"><input class="wide-input" type="text" id="http-body" value=""></div>
                    </div>
                  </p>
                </div>

              </div>
            </div>

            <div class="requester-builder-contents">
              <div class="requester-tab-contents">
                <div class="requester-tab-content layout-1-column">
                  <pre><code id="requestLog"></code></pre>
                </div>
                <div class="requester-tab-content layout-1-column">
                  <pre><code id="traceLog"></code></pre>
                </div>
              </div>
            </div>

          </div>
        </div>

      </div>

    </div>
  </div>

  <script>
    (function (exports) {
      'use strict'

      function str2bytes(string, encode = 'utf-8') {
        return new TextEncoder(encode).encode(string)
      }

      function bytes2str(uint8array, encode = 'utf-8') {
        return new TextDecoder(encode).decode(uint8array)
      }

      /**
       * Buffer 
       * @author xiaofeng
       * 
       * bytes : Uint8Array
       * 
       * +-------------------+------------------+------------------+
       * | prependable bytes |  readable bytes  |  writable bytes  |
       * |                   |     (CONTENT)    |                  |
       * +-------------------+------------------+------------------+
       * |                   |                  |                  |
       * V                   V                  V                  V
       * 0      <=      readerIndex   <=   writerIndex    <=     size
       */
      function Buffer(size) {
        this.bytes = new Uint8Array(size)
        this.writerIndex = 0
        this.readerIndex = 0
      }

      Buffer.fromBytes = function (bytes) {
        let buf = new Buffer(bytes.length * 2)
        buf.write(bytes)
        return buf
      }

      Buffer.fromString = function (str) {
        return Buffer.fromBytes(str2bytes(str))
      }

      Buffer.fromArrayBuffer = function (arraybuffer) {
        return Buffer.fromBytes(new Uint8Array(arraybuffer))
      }

      Buffer.prototype.readableBytes = function () {
        return this.writerIndex - this.readerIndex
      }

      Buffer.prototype.writableBytes = function () {
        return this.bytes.length - this.writerIndex
      }

      Buffer.prototype.prependableBytes = function () {
        return this.readerIndex
      }

      Buffer.prototype.capacity = function () {
        return this.bytes.length
      }

      Buffer.prototype.get = function (len) {
        len = Math.min(len, this.readableBytes())
        return this.rawRead(this.readerIndex, len)
      }

      Buffer.prototype.read = function (len) {
        len = Math.min(len, this.readableBytes())
        let read = this.rawRead(this.readerIndex, len)
        this.readerIndex += len
        if (this.readerIndex === this.writerIndex) {
          this.reset()
        }
        return read
      }

      Buffer.prototype.skip = function (len) {
        len = Math.min(len, this.readableBytes())
        this.readerIndex += len
        if (this.readerIndex === this.writerIndex) {
          this.reset()
        }
        return len
      }

      Buffer.prototype.readFull = function () {
        return this.read(this.readableBytes())
      }

      Buffer.prototype.writeArrayBuffer = function (arraybuffer) {
        return this.write(new Uint8Array(arraybuffer))
      }

      Buffer.prototype.writeString = function (str) {
        return this.write(str2bytes(str))
      }

      Buffer.prototype.write = function (bytes) {
        if (bytes.length === 0) {
          return
        }

        let len = bytes.length

        if (len <= this.writableBytes()) {
          this.rawWrite(this.writerIndex, bytes)
          this.writerIndex += len
          return
        }

        // expand
        if (len > (this.prependableBytes() + this.writableBytes())) {
          this.expand((this.readableBytes() + len) * 2)
        }

        // copy-move
        if (this.readerIndex !== 0) {
          this.bytes.copyWithin(0, this.readerIndex, this.writerIndex)
          this.writerIndex -= this.readerIndex
          this.readerIndex = 0
        }

        this.rawWrite(this.writerIndex, bytes)
        this.writerIndex += len
      }

      Buffer.prototype.reset = function () {
        this.readerIndex = 0
        this.writerIndex = 0
      }

      Buffer.prototype.toSring = function () {
        return bytes2str(this.bytes.slice(this.readerIndex, this.writerIndex))
      }

      // private
      Buffer.prototype.rawRead = function (offset, len) {
        if (offset < 0 || offset + len > this.bytes.length) {
          throw new RangeError('Trying to read beyond buffer length')
        }
        return this.bytes.slice(offset, offset + len)
      }

      // private
      Buffer.prototype.rawWrite = function (offset, bytes) {
        let len = bytes.length
        if (offset < 0 || offset + len > this.bytes.length) {
          throw new RangeError('Trying to write beyond buffer length')
        }
        for (let i = 0; i < len; i++) {
          this.bytes[offset + i] = bytes[i]
        }
      }

      // private
      Buffer.prototype.expand = function (size) {
        if (size <= this.bytes.capacity) {
          return
        }
        let buf = new Uint8Array(size)
        buf.set(this.bytes)
        this.bytes = buf
      }

      exports.Buffer = Buffer
      exports.str2bytes = str2bytes
      exports.bytes2str = bytes2str

    }(window))
  </script>

  <script>
    // http://stackoverflow.com/questions/4003823/javascript-getcookie-functions/4004010#4004010
    function getCookies() {
      var c = document.cookie, v = 0, cookies = {}
      if (document.cookie.match(/^\s*\$Version=(?:"1"|1);\s*(.*)/)) {
        c = RegExp.$1
        v = 1
      }
      if (v === 0) {
        c.split(/[,;]/).map(function (cookie) {
          var parts = cookie.split(/=/, 2),
            name = decodeURIComponent(parts[0].trimLeft()),
            value = parts.length > 1 ? decodeURIComponent(parts[1].trimRight()) : null
          cookies[name] = value
        })
      } else {
        c.match(/(?:^|\s+)([!#$%&'*+\-.0-9A-Z^`a-z|~]+)=([!#$%&'*+\-.0-9A-Z^`a-z|~]*|"(?:[\x20-\x7E\x80\xFF]|\\[\x00-\x7F])*")(?=\s*[,;]|$)/g).map(function ($0, $1) {
          var name = $0,
            value = $1.charAt(0) === '"'
              ? $1.substr(1, -1).replace(/\\(.)/g, "$1")
              : $1
          cookies[name] = value
        })
      }

      return cookies
    }

    function getCookie(name) {
      return getCookies()[name]
    }

    function JSONstringify(json) {
      if (typeof json != 'string') {
        json = JSON.stringify(json, undefined, '\t')
      }

      var
        arr = [],
        _string = 'color:green',
        _number = 'color:darkorange',
        _boolean = 'color:blue',
        _null = 'color:magenta',
        _key = 'color:red'

      json = json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
        var style = _number
        if (/^"/.test(match)) {
          if (/:$/.test(match)) {
            style = _key
          } else {
            style = _string
          }
        } else if (/true|false/.test(match)) {
          style = _boolean
        } else if (/null/.test(match)) {
          style = _null
        }
        arr.push(style)
        arr.push('')
        return '%c' + match + '%c'
      })

      arr.unshift(json)

      console.log.apply(console, arr)
    }
  </script>

  <script src="http://apps.bdimg.com/libs/highlight.js/9.1.0/highlight.min.js"></script>
  <script src="http://apps.bdimg.com/libs/highlight.js/9.1.0/languages/json.min.js"></script>

  <script id="worker_highlight" type="javascript/worker">
    self.onmessage = function(event) { importScripts('http://apps.bdimg.com/libs/highlight.js/9.1.0/highlight.min.js'); importScripts('http://apps.bdimg.com/libs/highlight.js/9.1.0/languages/json.min.js');
    let result = self.hljs.highlightAuto(event.data); self.postMessage(result.value); }
  </script>

  <script>
    function highlightBg(codeEl) {
      let blob = new Blob([document.querySelector('#worker_highlight').textContent], { type: "text/javascript" })
      let worker = new Worker(window.URL.createObjectURL(blob))
      worker.onmessage = function (event) {
        codeEl.innerHTML = event.data
      }
      worker.postMessage(codeEl.textContent)
    }

    function highlight(code) {
      return hljs.highlightAuto(code).value
      // console.log(hljs.highlightAuto(code).value)
      // console.log(hljs.highlight("json", code).value)
    }

    const log = (function () {
      let traceLog = document.querySelector("#traceLog")
      let requestLog = document.querySelector("#requestLog")

      let trace = function (el) {
        return function (str) {
          if (!str) {
            return
          }
          if (str.length > 0 && str.charAt(str.length - 1) != '\n') {
            str += '\n'
          }
          // var t = new Date().toLocaleTimeString()
          el.innerText = /*t + '  ' + */ str + el.innerText
          // el.innerText = highlight(str + el.innerText)

          // 高亮返回值
          highlightBg(el)

          // 高亮console json
          JSONstringify(str)
        }
      }

      return {
        trace: trace(traceLog),
        response: trace(requestLog)
      }
    })()


    function Trace(addr, port) {
      this.isConnected = false
      this.addr = addr
      this.port = port
      this.buffer = new Buffer(1024 * 1024)
    }

    Trace.prototype.start = function (onStart, onClose) {
      this.onStart = onStart
      this.onClose = onClose

      let wsServer = 'ws://' + this.addr + ':' + this.port

      let ws = new WebSocket(wsServer)
      if (!ws) {
        return
      }

      // 使用ArrayBuffer接收的是二进制数据
      ws.binaryType = 'arraybuffer'

      ws.addEventListener('open', function (evt) {
        // 无API可以获取websocket http response的header信息，这里使用cookie不准确
        // response回来时设置的cookie可能在ws连接事件完成之前被改变 ？！
        this.sid = getCookie('sid')
        this.isConnected = true
        console.info("Connected to WebSocket server.")
        this.onStart(evt)
      }.bind(this))

      ws.addEventListener('close', function (evt) {
        this.isConnected = false
        console.info("Disconnected")
        this.onClose(evt)
      }.bind(this))

      ws.addEventListener('error', function (evt, e) {
        this.isConnected = false
        console.error('Error occured: ' + evt.data)
        this.onClose(evt)
      }.bind(this))

      ws.addEventListener('message', function (evt) {
        // 处理粘包
        this.buffer.writeArrayBuffer(evt.data)

        while (true) {
          if (this.buffer.readableBytes() < 4) {
            return
          }

          var arraybuffer = this.buffer.get(4).buffer
          var len = new DataView(arraybuffer).getUint32() // PHP pack('N')
          if (this.buffer.readableBytes() < len + 4) {
            return
          }

          this.buffer.skip(4)

          var str = bytes2str(this.buffer.read(len))
          if (str === "PING") {
            // 单独处理心跳
            this.send("PONG")
          } else {
            try {
              var traceData = JSON.parse(str)
              console.log(traceData)
              log.trace(JSON.stringify(traceData, null, 2))
            } catch (e) {
              console.error(e)
            }
          }
        }
      }.bind(this))

      this.ws = ws
    }

    Trace.prototype.stop = function () {
      // this.ws.readyState !== WebSocket.CLOSED 
      if (this.ws && this.isConnected) {
        this.ws.close()
      }
    }

    Trace.prototype.send = function (toSend) {
      // trace.ws.readyState === WebSocket.OPEN
      if (this.ws && this.isConnected) {
        return this.ws.send(toSend)
      } else {
        return false
      }
    }

    let trace

    let initRequest = (function () {
      let el = document.getElementById('protocol-select')
      return function () {
        let evt = document.createEvent('HTMLEvents')
        evt.initEvent('change', false, true)
        el.dispatchEvent(evt)
      }
    }())

    let swithRequest = (function () {
      let $el = document.getElementById('request-args')
      let $url = document.getElementById('url')

      let $method = document.getElementById('http-method')
      let $header = document.getElementById('http-header')
      let $cookie = document.getElementById('http-cookie')
      let $body = document.getElementById('http-body')

      let $novaArgs = document.getElementById('nova-args')
      let $novaAttach = document.getElementById('nova-attach')

      return function (protocol = 'none') {
        $el.className = 'protocol-' + protocol
        if (protocol === 'http') {
          $url.value = 'http://10.9.10.29:8000/card/card/getCard.json?cardAlias=2fst2eprra468i&csrf_token=Qls%2FeQwDKiISCnkuAmQqPAtQbjs8IUcRGgEvKiYkDXINBT1tDDFVOwBEMicqIxAyDkwtICRFIDgTHCFuFj4nKFRAJSp0MiskAVUrPjF0ICNQWigvVwgsLRIkOi5BJCFlUSEnOlE%2FZCFbbgYgVA'
          $cookie.value = '{"sid":"d0ffe2125bc1cde7eb64167efd1c31f4","kdtnote_fans_id":"0","kdt_id":"160","team_auth_key":"385f9ce644eabd0ba263165fc8896671","mp_id":"8"}'
        } else if (protocol === 'nova') {
          $url.value = 'nova://10.9.142.174:8051/com.youzan.knowledge.knowledge.service.KnowledgeService.getListWithStripContent'
          $novaArgs.value = '{"keyword":"销售员", "page":1, "pageSize":20}'
          $novaAttach.value = '{"":""}'
        }
      }
    }())

    let startServer = (function () {
      let el = document.querySelector(".requester-builder-header")

      return function () {
        // trace.ws.readyState !== WebSocket.OPEN
        if (trace && trace.isConnected) {
          return
        }

        var addr = window.location.hostname
        var port = window.location.port ? window.location.port : 80
        trace = new Trace(addr, port)
        trace.start(function () {
          initRequest()
          // document.querySelector(".connect-to").innerText = addr + ":" + port
          el.className = "requester-builder-header connected"
        }, function () {
          swithRequest('none')
          el.className = "requester-builder-header"
        })
      }
    }())

    function stopServer() {
      // trace.ws.readyState !== WebSocket.CLOSED
      if (trace && trace.isConnected) {
        trace.stop()
        swithRequest('none')
      }
    }



    document.getElementById('connectServer').addEventListener('click', startServer)
    document.getElementById('disconnectServer').addEventListener('click', stopServer)
    document.getElementById('doSend').addEventListener('click', (function () {
      let sendSwitch = (function () {
        let $send = document.getElementById('doSend')
        return {
          on: () => { $send.disabled = ''; $send.innerText = 'Send' },
          off: () => { $send.disabled = 'disabled'; $send.innerText = 'Wait' }
        }
      }())
      let $protocol = document.getElementById('protocol-select')
      let $url = document.getElementById('url')

      let $method = document.getElementById('http-method')
      let $header = document.getElementById('http-header')
      let $cookie = document.getElementById('http-cookie')
      let $body = document.getElementById('http-body')

      let $novaArgs = document.getElementById('nova-args')
      let $novaAttach = document.getElementById('nova-attach')
      return function () {
        // 首先清空trace记录
        traceLog.innerText = ''

        let data = {
          protocol: $protocol.value,
          uri: $url.value
        }
        if (data.protocol === 'http') {
          data.method = $method.value
          data.header = $header.value
          data.cookie = $cookie.value
          data.body = $body.value
        } else if (data.protocol === 'nova') {
          data.args = $novaArgs.value
          data.attach = $novaArgs.value
        } else {
          return
        }

        sendSwitch.off()
        // 请求带上ws的sid(fd), 识别report的trace信息归属的哪个ws连接
        fetch('./request?sid=' + trace.sid, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(data),
        })
          .then(
          function (response) {
            sendSwitch.on()
            if (response.status !== 200) {
              console.error('request error. Status Code: ' + response.status)
              return
            }
            // response.json()
            return response.text()
          }
          )
          .then(function (text) {
            try {
              var resp = JSON.parse(text)
              console.log(resp)
              log.response(JSON.stringify(resp, null, 2))
            } catch (e) {
              log.response(text)
            }
          })
          .catch(function (err) {
            sendSwitch.on()
            console.log('Fetch Error: ', err)
            log.response('fetch error')
          })
      }
    })())

    document.getElementById('logClear').addEventListener('click', (function () {
      var traceLog = document.querySelector("#traceLog")
      var requestLog = document.querySelector("#requestLog")
      return function () {
        traceLog.innerText = ''
        requestLog.innerText = ''
        console.clear()
      }
    })())

    document.getElementById('protocol-select').addEventListener('change', function (e) {
      swithRequest(e.srcElement.value)
    })

    document.addEventListener('DOMContentLoaded', function () {
      let isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor)
      if (isChrome) {
        document.getElementById('connectServer').click()
      } else {
        alert("请更换Chrome浏览器访问!!!")
      }
    })
  </script>
</body>

</html>
HTML
);
    }
}



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

        // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
        $type = self::$t_call; // call
        $ver1 = self::$ver1 | $type;

        $payload .= pack('N', $ver1);
        $payload .= pack('N', strlen("invoke"));
        $payload .= "invoke";
        $payload .= pack('N', $seq);

        // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
        // {{{ pack args
        $fieldId = 1;
        $fieldType = 12; // struct
        $payload .= pack('c', $fieldType); // byte
        $payload .= pack('n', $fieldId); //u16

        // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
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
        // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-

        $payload .= pack('c', 0); // stop
        // pack arg end }}}
        // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-

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

function sys_echo($context) {
    $workerId = isset($_SERVER["WORKER_ID"]) ? " #" . $_SERVER["WORKER_ID"] : "";
    $dataStr = date("Y-m-d H:i:s", time());
    echo "[{$dataStr}{$workerId}] $context\n";
}

function array_get(array $arr, $key, $default = null) {
    if (isset($arr[$key])) {
        return $arr[$key];
    } else {
        return $default;
    }
}

function json_parse($str, &$err = null) {
    if ($str === "") {
        return false;
    }

    $array = json_decode($str, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $err = json_last_error_msg();
        return false;
    }
    return $array;
}