<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/5/19
 * Time: 下午4:00
 */
namespace Zan\Framework\Network\ServerManager;

use Com\Youzan\Nova\Framework\Generic\Servicespecification\GenericService;
use Kdt\Iron\Nova\Service\Registry;
use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Core\Debug;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Utilities\Types\Time;
use Kdt\Iron\Nova\Nova;
use Zan\Framework\Network\Common\Curl;

class ServiceUnregister
{
    private $config = [];

    public function __construct()
    {
        // $this->init();
        // TODO 移除
        // $this->initGenericService();
    }

//    private function init()
//    {
//        $this->config['services'] = Nova::getAvailableService();
//    }

//    private function initGenericService()
//    {
//        $registry = new Registry();
//        $registry->register(new GenericService());
//        $genericServices = $registry->getAll();
//        if ($genericServices) {
//            array_push($this->config['services'], ...$genericServices);
//        }
//    }

    private function parseConfig($config)
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

    public function unRegister()
    {
        $haunt = Config::get('haunt.unregister');
        if (empty($haunt)) {
            return;
        }

        $isRegistered = ServerRegisterInitiator::getInstance()->getRegister();
        if ($isRegistered == ServerRegisterInitiator::DISABLE_REGISTER) {
            return;
        }

        $keys = Nova::getEtcdKeyList();
        foreach ($keys as list($protocol, $domain, $appName)) {
            $this->doUnRegisterOneGroup($protocol, $domain, $appName);
        }

    }

    private function doUnRegisterOneGroup($protocol, $domain, $appName)
    {
        $config = [];
        $config["services"] = Nova::getAvailableService($protocol, $domain, $appName);
        $config["domain"] = $domain;
        $config["appName"] = $appName;
        $config["protocol"] = $protocol;
        $this->toUnRegister($config);
    }

    private function toUnRegister($config)
    {
        $haunt = Config::get('haunt');
        $url = 'http://'.$haunt['unregister']['host'].':'.$haunt['unregister']['port'].$haunt['unregister']['uri'];
        $curl = new Curl();
        $body = $this->parseConfig($config);
        fprintf(STDERR, "\nunRegister: \n");
        fprintf(STDERR, json_encode($body, JSON_PRETTY_PRINT) . "\n\n");
        $unregister = $curl->post($url, $body);
        if (Debug::get()) {
            sys_echo($unregister);
        }
    }
}