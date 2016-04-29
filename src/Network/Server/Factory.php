<?php

namespace Zan\Framework\Network\Server;

use RuntimeException;
use Zan\Framework\Foundation\Container\Di;
use Zan\Framework\Foundation\Core\Config;
use swoole_http_server as SwooleHttpServer;
use swoole_server as SwooleTcpServer;
use Zan\Framework\Network\Http\Server as HttpServer;
use Zan\Framework\Network\Tcp\Server as TcpServer;

class Factory
{
    /**
     * @return \Zan\Framework\Network\Http\Server
     */
    public function createHttpServer()
    {
        $config = Config::get('server');
        if (empty($config)) {
            throw new RuntimeException('http server config not found');
        }

        $host = $config['host'];
        $port = $config['port'];
        $config = $config['config'];
        if (empty($host) || empty($port)) {
            throw new RuntimeException('http server config error: empty ip/port');
        }

        $swooleServer = Di::make(SwooleHttpServer::class, [$host, $port], true);

        $server = Di::make(HttpServer::class, [$swooleServer, $config]);

        return $server;
    }

    /**
     * @return \Zan\Framework\Network\Tcp\Server
     */
    public function createTcpServer()
    {
        $config = Config::get('server');
        if (empty($config)) {
            throw new RuntimeException('tcp server config not found');
        }

        $host = $config['host'];
        $port = $config['port'];
        $config = $config['config'];
        if (empty($host) || empty($port)) {
            throw new RuntimeException('tcp server config error: empty ip/port');
        }

        $swooleServer = Di::make(SwooleTcpServer::class, [$host, $port], true);

        $server = Di::make(TcpServer::class, [$swooleServer, $config]);

        return $server;
    }
}