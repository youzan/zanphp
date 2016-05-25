<?php
/*
 *    Copyright 2012-2016 Youzan, Inc.
 *
 *    Licensed under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an "AS IS" BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
 */
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