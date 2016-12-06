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
        $this->init();
        $this->initGenericService();
    }

    private function init()
    {
        $this->config['services'] = Nova::getAvailableService();
    }

    private function initGenericService()
    {
        $registry = new Registry();
        $registry->register(new GenericService());
        $genericServices = $registry->getAll();
        if ($genericServices) {
            array_push($this->config['services'], ...$genericServices);
        }
    }

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
                    'Namespace' => 'com.youzan.service',
                    'SrvName' => Application::getInstance()->getName(),
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

    public function unregister()
    {
        $haunt = Config::get('haunt.unregister');
        if (empty($haunt)) {
            return;
        }

        $isRegistered = ServerRegisterInitiator::getInstance()->getRegister();
        if ($isRegistered == ServerRegisterInitiator::DISABLE_REGISTER) {
            return;
        }
        $this->toUnregister();
    }

    private function toUnregister()
    {
        $haunt = Config::get('haunt');
        $url = 'http://'.$haunt['unregister']['host'].':'.$haunt['unregister']['port'].$haunt['unregister']['uri'];
        $curl = new Curl();
        $unregister = $curl->post($url, $this->parseConfig($this->config));
        if (Debug::get()) {
            sys_echo($unregister);
        }
    }
}