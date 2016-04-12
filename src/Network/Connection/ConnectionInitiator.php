<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/5
 * Time: 11:59
 */

namespace Zan\Framework\Network\Connection;


use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Connection\Factory\Redis;
use Zan\Framework\Network\Connection\Factory\Syslog;
use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Network\Connection\Factory\Http;
use Zan\Framework\Network\Connection\Factory\Mysqli;


class ConnectionInitiator
{
    use Singleton;

    private $engineMap =['mysqli', 'http', 'redis', 'syslog'];


    public function __construct()
    {
    }

    /**
     * @param array $config(=Config::get('connection'))
     */
    public function init(array $config)
    {
        //读取配置文件
        //$configs = Config::get('xxxx');
        $configs = $this->configFile();
        $this->initConfig($configs);

    }


    private function initConfig($config)
    {
        foreach ($config as $cf) {
            if (!isset($cf['engine'])) {
                $this->initConfig($cf);
            } else {
                if (empty($cf['pool'])) {
                    continue;
                }
                //创建连接池
                $drive = $cf['engine'];
                if (in_array($drive, $this->engineMap)) {
                    $drive = ucfirst($drive);
                    $this->initPool($drive, $cf['pool']);
                }
            }

        }
    }

    /**
     * @param $driver
     * 初始化连接池
     */
    private function initPool($driver, $config)
    {
        switch ($driver) {
            case 'Redis':
                $factory = new Redis($config);
            case 'Syslog':
                $factory = new Syslog($config);
            case 'Http':
                $factory = new Http($config);
            case 'Mysqli':
                $factory = new Mysqli($config);
            default:
                $connectionPool = new Pool($factory, $config, $driver);
                $connectionManage = new ConnectionManager();
                $connectionManage->addPool($config['pool_name'], $connectionPool);
        }
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