<?php

namespace Zan\Framework\Network\Server;

use RuntimeException;
use Zan\Framework\Foundation\Container\Di;
use Zan\Framework\Foundation\Core\Config;
use swoole_http_server as SwooleHttpServer;
use swoole_server as SwooleTcpServer;
use Zan\Framework\Network\Http\Server as HttpServer;
use Zan\Framework\Network\Tcp\Server as TcpServer;
use Zan\Framework\Network\MqSubscribe\Server as MqServer;

class Factory
{
    private $configFile;
    private $host;
    private $port;
    private $serverConfig;

    public function __construct($configFile)
    {
        $this->configFile = $configFile;
    }

    private function validConfig()
    {
        $config = Config::get($this->configFile);
        if (empty($config)) {
            throw new RuntimeException('server config not found');
        }

        $this->host = $config['host'];
        $this->port = $config['port'];
        $this->serverConfig = $config['config'];
        if (empty($this->host) || empty($this->port)) {
            throw new RuntimeException('server config error: empty ip/port');
        }

        // 强制关闭swoole worker自动重启(未考虑请求处理完), 使用zan框架重启机制
        $this->serverConfig["max_request"] = 0;
    }

    /**
     * @return \Zan\Framework\Network\Http\Server
     */
    public function createHttpServer()
    {
        $this->validConfig();

        $swooleServer = Di::make(SwooleHttpServer::class, [$this->host, $this->port], true);

        $server = Di::make(HttpServer::class, [$swooleServer, $this->serverConfig]);

        return $server;
    }

    /**
     * @return \Zan\Framework\Network\Tcp\Server
     */
    public function createTcpServer()
    {
        $this->validConfig();

        $swooleServer = Di::make(SwooleTcpServer::class, [$this->host, $this->port], true);

        $server = Di::make(TcpServer::class, [$swooleServer, $this->serverConfig]);

        return $server;
    }

    /**
     * @return \Zan\Framework\Network\MqSubscribe\Server
     */
    public function createMqServer()
    {
        $this->validConfig();

        $swooleServer = Di::make(SwooleHttpServer::class, [$this->host, $this->port], true);

        $server = Di::make(MqServer::class, [$swooleServer, $this->serverConfig]);

        return $server;
    }
}