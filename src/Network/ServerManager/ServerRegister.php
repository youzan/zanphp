<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/5/19
 * Time: 下午4:00
 */
namespace Zan\Framework\Network\ServerManager;

use Zan\Framework\Foundation\Core\Env;
use Zan\Framework\Network\Common\HttpClient;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Common\Curl;

class ServerRegister
{
    public function parseConfig($config)
    {
        $extData = [];
        $ip = nova_get_ip();
        foreach ($config['services'] as $service) {
            $extData[] = [
                'service' => $service['service'],
                'language' => 'php',
                'timestamp' => time(),
                'version' => '1.0.0',
                'methods' => $service['methods'],
            ];
        }
        return [
            'SrvList' => [
                [
                    'Namespace' => 'com.youzan.service',
                    'SrvName' => $config['module'],
                    'IP' => $ip,
                    'Port' => (int)Config::get('server.port'),
                    'Protocol' => 'nova',
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
        yield $httpClient->post($haunt['register']['uri'], $this->parseConfig($config), null);
    }


}