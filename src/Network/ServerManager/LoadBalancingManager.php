<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/5/19
 * Time: 下午4:01
 */
namespace Zan\Framework\Network\ServerManager;

use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Network\ServerManager\LoadBalancingPool;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Connection\Factory\NovaClient as NovaClientFactory;

class LoadBalancingManager
{
    use Singleton;

    /**
     * @var LoadBalancingPool
     */
    private $loadBalancingPool;

    public function work($servers)
    {
        $config = Config::get('loadBalancing');
        $novaConfig = Config::get('novaConnection');
        foreach ($servers as $server) {
            $novaConfig['host'] = $server['host'];
            $novaConfig['port'] = $server['port'];
            $config['connections'][] = $novaConfig;
        }
        $novaClientFactory = new NovaClientFactory($config['connections']);
        $this->loadBalancingPool = new LoadBalancingPool($novaClientFactory, $config, 'novaClient');
    }

    public function get()
    {
        yield $this->loadBalancingPool->get();
    }

    public function offline()
    {

    }

    public function addOnline()
    {

    }
}