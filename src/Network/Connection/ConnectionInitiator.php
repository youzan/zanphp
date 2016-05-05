<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/5
 * Time: 11:59
 */

namespace Zan\Framework\Network\Connection;


use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Connection\Factory\NovaClient;
use Zan\Framework\Network\Connection\Factory\Redis;
use Zan\Framework\Network\Connection\Factory\Syslog;
use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Network\Connection\Factory\Http;
use Zan\Framework\Network\Connection\Factory\Mysqli;


class ConnectionInitiator
{
    use Singleton;

    private $engineMap = [
        'mysqli', 
        'http', 
        'redis', 
        'syslog', 
        'novaClient',
    ];

    public function __construct()
    {
    }

    /**
     * @param array $config(=Config::get('connection'))
     */
    public function init($config, $server)
    {
        $connectionManager = ConnectionManager::getInstance();
        $connectionManager->setServer($server);
        
        if (is_array($config)) {
            $this->initConfig($config);
        }
        
        $connectionManager->monitor();
    }

    private function initConfig($config)
    {
        if (!is_array($config)) {
            return false; 
        }
        foreach ($config as $cf) {
            if (!isset($cf['engine'])) {
                if (is_array($config)) {
                    $this->initConfig($cf);
                }
                continue;
            } 
            
            if (empty($cf['pool'])) {
                continue;
            }
            
            $factoryType = $cf['engine'];
            if (in_array($factoryType, $this->engineMap)) {
                $factoryType = ucfirst($factoryType);
                $this->initPool($factoryType, $cf['pool']);
            }
        }
    }

    /**
     * @param $factoryType
     * @param $config
     */
    private function initPool($factoryType, $config)
    {
        switch ($factoryType) {
            case 'Redis':
                $factory = new Redis($config);
                break;
            case 'Syslog':
                $factory = new Syslog($config);
                break;
            case 'Http':
                $factory = new Http($config);
                break;
            case 'Mysqli':
                $factory = new Mysqli($config);
                break;
            case 'NovaClient':
                $factory = new NovaClient($config);
                break;
            default:
                break;
        }
        $connectionPool = new Pool($factory, $config, $factoryType);
        ConnectionManager::getInstance()->addPool($config['pool_name'], $connectionPool);
    }
}