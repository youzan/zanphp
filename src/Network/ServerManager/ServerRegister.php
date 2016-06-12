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
        @exec("ifconfig", $ifconfig);
        $ip = '';
        foreach ($ifconfig as $value) {
            preg_match_all('/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})+/', $value, $match);
            if (empty($match[0])) {
                continue;
            }
            if ($match[0][0] != '127.0.0.1') {
                $ip = $match[0][0];
                break;
            }
        }
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
            'IP' => $ip,
            'Port' => Config::get('server.port'),
            'Protocol' => 'nova',
            'Status' => 1,
            'Weight' => 100,
            'ExtData' => json_encode($extData),
        ];
    }

    public function register($config)
    {
        $haunt = Config::get('haunt');
        $curl = new Curl();
        return $curl->post($haunt['register']['uri'], $this->parseConfig($config), $haunt['register']['timeout']);
    }


}