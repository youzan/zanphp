<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/5/19
 * Time: 下午4:00
 */
namespace Zan\Framework\Network\ServerManager;

use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Core\Env;
use Zan\Framework\Network\Common\HttpClient;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Common\Curl;
use Zan\Framework\Network\Server\Timer\Timer;
use Zan\Framework\Utilities\Types\Time;
use Zan\Framework\Foundation\Coroutine\Task;

class ServerRegister
{
    public function parseConfig($config)
    {
        $extData = [];
        $ip = nova_get_ip();
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
                    'Namespace' => $config["domain"],
                    'SrvName' => $config["appName"],
                    'IP' => $ip,
                    'Port' => (int)Config::get('server.port'),
                    'Protocol' => $config["protocol"],
                    'Status' => 1,
                    'Weight' => 100,
                    'ExtData' => json_encode($extData),
                ]
            ]
        ];
    }

    public function register($config)
    {
        $haunt = Config::get('haunt');
        $httpClient = new HttpClient($haunt['register']['host'], $haunt['register']['port']);
        $body = $this->parseConfig($config);
        $detail = $this->inspect($body['SrvList'][0]);
        sys_echo("registering [$detail]");

        try {
            $response = (yield $httpClient->postJson($haunt['register']['uri'], $body, null));
            $msg = rtrim($response->getBody(), "\n");
            sys_echo("$msg [$detail]");
        } catch (\Exception $e) {
            sys_error("register failed: ".$haunt['register']['host'].":".$haunt['register']['port']);
            Timer::after(3000, function () use ($config) {
                Task::execute($this->register($config));
            });
        }
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