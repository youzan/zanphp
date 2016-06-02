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

class ServerRegister
{
    public function parseConfig($config)
    {
        $extData = [];
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
            'Namespace' => 'com.youzan.service',
            'SrvName' => $config['module'],
            'IP' => Env::get('ip'),
            'Port' => 8000,
            'Protocol' => 'nova',
            'Status' => 1,
            'Weight' => 0,
            'ExtData' => json_encode($extData),
        ];
    }

    public function register($config)
    {
        $haunt = Config::get('haunt');
        $httpClient = new HttpClient($haunt['register']['host'], $haunt['register']['port']);
        yield $httpClient->post($haunt['register']['uri'], $this->parseConfig($config), $haunt['register']['timeout']);
    }


}