<?php

namespace Zan\Framework\Network\ServerManager;

use Zan\Framework\Network\Common\HttpClient;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Common\Response;
use Zan\Framework\Network\Server\Timer\Timer;
use Zan\Framework\Network\ServerManager\Exception\ServerConfigException;
use Zan\Framework\Utilities\Types\Time;
use Zan\Framework\Foundation\Coroutine\Task;

class ServerRegister
{
    const DEFAULT_ETD_TTL = 25;

    public static function createEtcdV2KV($config, $status = ServerDiscovery::SRV_STATUS_OK)
    {
        // key := "/" + server.Protocol + ":" + server.Namespace + "/" + server.SrvName + "/" + server.IP + ":" + strconv.Itoa(server.Port)

        $protocol = $config["protocol"];
        $namespace = $config["domain"];
        $srvName = $config["appName"];
        $ip = nova_get_ip();
        $port = Config::get('server.port');

        $extData = [];
        foreach ($config['services'] as $service) {
            $extData[] = [
                'language'=> 'php',
                'version' => '1.0.0',
                'timestamp'=> Time::stamp(),
                'service' => $service['service'],
                'methods' => $service['methods'],
            ];
        }

        $etcdV2Key = "$protocol:$namespace/$srvName/$ip:$port";

        $etcdV2Value = [
            'Namespace' => $namespace,
            'SrvName' => $srvName,
            'IP' => $ip,
            'Port' => (int)$port,
            'Protocol' => $protocol,
            'Status' => $status,
            'Weight' => 100,
            'ExtData' => json_encode($extData),
        ];

        return [$etcdV2Key, $etcdV2Value];
    }

    public static function getRandEtcdNode()
    {
        $nodes = Config::get("zan_registry.etcd.nodes", []);
        if (empty($nodes)) {
            throw new ServerConfigException("empty etcd nodes in zan_registry.etcd.nodes");
        }
        return $nodes[array_rand($nodes)];
    }

    public static function createHauntBody($config, $status = ServerDiscovery::SRV_STATUS_OK)
    {
        $protocol = $config["protocol"];
        $namespace = $config["domain"];
        $srvName = $config["appName"];
        $ip = nova_get_ip();
        $port = Config::get('server.port');

        $extData = [];
        foreach ($config['services'] as $service) {
            $extData[] = [
                'language'=> 'php',
                'version' => '1.0.0',
                'timestamp'=> Time::stamp(),
                'service' => $service['service'],
                'methods' => $service['methods'],
            ];
        }

        return [
            'SrvList' => [
                [
                    'Namespace' => $namespace,
                    'SrvName' => $srvName,
                    'IP' => $ip,
                    'Port' => (int)$port,
                    'Protocol' => $protocol,
                    'Status' => $status,
                    'Weight' => 100,
                    'ExtData' => json_encode($extData),
                ]
            ]
        ];
    }

    public function register($config)
    {
        $type = Config::get("registry.type", "etcd");
        if ($type === "etcd") {
            yield $this->registerToEtcdV2($config);
        } else if($type === "haunt") {
            yield $this->registerByHaunt($config);
        }
    }

    private function registerToEtcdV2($config, $isRefresh = false)
    {
        $node = static::getRandEtcdNode();

        list($etcdV2Key, $etcdV2Value) = static::createEtcdV2KV($config);
        $detail = $this->inspect($etcdV2Value);

        if ($isRefresh === false) {
            sys_echo("registering [$detail]");
        }

        $httpClient = new HttpClient($node["host"], $node["port"]);
        $httpClient->setMethod("PUT");
        // WARNING	php_swoole_add_timer: cannot use timer in master process.
        // $httpClient->setTimeout(3000);
        $httpClient->setUri("/v2/keys/$etcdV2Key");
        $httpClient->setBody(http_build_query([
            "value" => json_encode($etcdV2Value),
            "ttl" => static::DEFAULT_ETD_TTL,
        ]));
        $httpClient->setHeader([
            'Content-Type' => 'application/x-www-form-urlencoded'
        ]);

        try {
            /** @var Response $response */
            $response = (yield $httpClient->build());
            $statusCode = $response->getStatusCode();
            $body = $response->getBody();
            if ($statusCode >= 200 && $statusCode < 300) {
                if ($isRefresh === false) {
                    sys_echo("Register to etcd success [code=$statusCode]");
                }
                // WARNING	php_swoole_add_timer: cannot use timer in master process.
                // $this->refreshingTTL($config);
                return;
            } else {
                sys_error("status=$statusCode, body=$body");
            }
        }
        catch (\Throwable $e) { }
        catch (\Exception $e) { }

        if (isset($e)) {
            echo_exception($e);
        }

        if ($isRefresh) {
            $desc = "refresh etcd ttl";
        } else {
            $desc = "register";
        }

        sys_error("$desc failed: ".$node["host"].":".$node["port"]);

        Timer::after(1000, function () use ($config) {
            $co = $this->registerToEtcdV2($config);
            Task::execute($co);
        });
    }

    public function refreshingEtcdV2TTL($config)
    {
        $type = Config::get("registry.type", "etcd");
        if ($type === "etcd") {
            $interval = (static::DEFAULT_ETD_TTL - 5) * 1000;
            Timer::tick($interval, function() use($config) {
                $co = $this->registerToEtcdV2($config, true);
                Task::execute($co);
            });
        }
    }

    private function registerByHaunt($config)
    {
        $haunt = Config::get('registry.haunt');

        $httpClient = new HttpClient($haunt['register']['host'], $haunt['register']['port']);
        $body = static::createHauntBody($config);
        $detail = $this->inspect($body['SrvList'][0]);
        sys_echo("registering [$detail]");

        try {
            $response = (yield $httpClient->postJson($haunt['register']['uri'], $body, null));
            $msg = rtrim($response->getBody(), "\n");
            sys_echo("$msg [$detail]");
            return;
        }
        catch (\Throwable $e) { }
        catch (\Exception $e) { }

        echo_exception($e);
        sys_error("register failed: ".$haunt['register']['host'].":".$haunt['register']['port']);
        Timer::after(1000, function () use ($config) {
            $co = $this->registerByHaunt($config);
            Task::execute($co);
        });
    }

    private function inspect($config)
    {
        $map = [];
        foreach ($config as $k => $v) {
            if ($k === "ExtData") {
                continue;
            }
            $map[] = "$k=$v";
        }
        return implode(", ", $map);
    }
}