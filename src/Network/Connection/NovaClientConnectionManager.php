<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/5/19
 * Time: 下午4:01
 */
namespace Zan\Framework\Network\Connection;

use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Network\Connection\NovaClientPool;
use Zan\Framework\Foundation\Core\Config;


class NovaClientConnectionManager
{
    use Singleton;

    /**
     * @var NovaClientPool
     */
    private $novaClientPool;

    public function work($servers)
    {
        $config = Config::get('loadBalancing');
        $novaConfig = Config::get('novaConnection');
        foreach ($servers as $server) {
            $novaConfig['host'] = $server['host'];
            $novaConfig['port'] = $server['port'];
            $config['connections'][] = $novaConfig;
        }
        $this->novaClientPool = new NovaClientPool($config);
    }

    public function get()
    {
        yield $this->novaClientPool->get();
    }

    public function offline()
    {

    }

    public function addOnline()
    {

    }
}