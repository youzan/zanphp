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

    private $engineMap =['mysqli', 'http', 'redis', 'syslog', 'novaClient'];

    public $poolName = '';


    public function __construct()
    {
    }

    /**
     * @param $directory
     */
    public function init($directory)
    {
        if(!empty($directory)) {
            $this->poolName = $directory;
            $this->initConfig();
        }
    }


    private function initConfig()
    {
        $config = Config::get($this->poolName);
        if (is_array($config)) {
            foreach ($config as $k=>$cf) {
                if (!isset($cf['engine'])) {
                    if (is_array($config)) {
                        $this->poolName = $this->poolName . '.' . $k;
                        $this->initConfig($cf);
                    }
                } else {
                    if (empty($cf['pool'])) {
                        continue;
                    }
                    //创建连接池
                    $this->poolName = $this->poolName . '.' . $k;
                    $factoryType = $cf['engine'];
                    if (in_array($factoryType, $this->engineMap)) {
                        $factoryType = ucfirst($factoryType);
                        $cf['pool']['pool_name'] = $this->poolName;
                        $this->initPool($factoryType, $cf['pool']);
                    }
                }

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

    private function configFile()
    {
        $config = [
            'mysql' => [
                'default_write' => [
                    'engine'=> 'mysqli',
                    'pool'  => [
                        'pool_name' => 'pifa',
                        'maximum-connection-count' => '50',
                        'minimum-connection-count' => '10',
                        'keeping-sleep-time' => '10',
                        'init-connection'=> '10',
                        'host' => '192.168.66.202',
                        'user' => 'test_koudaitong',
                        'password' => 'nPMj9WWpZr4zNmjz',
                        'database' => 'pf',
                        'port' => '3306'
                    ],
                ],
                'default_read' => [
                    'engine'=> 'mysqli',
                    'pool'  => [],
                ],
            ],
            'http' => [
                'default' => [
                    'engine' => 'http',
                    'pool' => []
                ]
            ]
        ];
        return $config;
    }


}