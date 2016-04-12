<?php
/**
 * Created by PhpStorm.
 * User: liuxinlong
 * Date: 16/4/12
 * Time: 10:15
 */


namespace Zan\Framework\Test\Network\Connection;

use Zan\Framework\Network\Connection\ConnectionInitiator;

class ConnectionInitiatorTest  extends \TestCase{

    public function testInitByConfigWork()
    {
        $ci = new ConnectionInitiator();
        $ci->init([]);
    }

    private function initPool($config)
    {
        foreach ($config as $cf) {
            if (!isset($cf['engine'])) {
                $this->initPool($cf);
            } else {
                if (empty($cf['pool'])) {
                    continue;
                }
                var_dump($cf['pool']['pool_name']);
            }
//            if (isset($cf['engine']) && !empty($cf['pool'])) {
//
//            } else {
//                continue;
//            }

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
                    'pool'  => [
                        'pool_name' => 'pf-2'
                    ],
                ],
            ],
            'http' => [
                'default' => [
                    'engine' => 'http',
                    'pool' => [
                        'pool_name' => 'http',
                    ]
                ]
            ]
        ];
        return $config;
    }
}