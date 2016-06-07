<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/5/19
 * Time: 下午4:01
 */
namespace Zan\Framework\Network\Connection;

use Zan\Framework\Contract\Network\Connection;
use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Network\Connection\NovaClientPool;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Connection\Exception\CanNotFindServiceNamePoolException;

class NovaClientConnectionManager
{
    use Singleton;

    private $novaClientPool = [];

    public function work($module, $servers)
    {
        $config = Config::get('loadBalancing');
        $novaConfig = Config::get('connection.nova');
        foreach ($servers as $server) {
            $novaConfig['host'] = $server['host'];
            $novaConfig['port'] = $server['port'];
            $config['connections'][] = $novaConfig;
        }
        $this->novaClientPool[$module] = new NovaClientPool($config);
    }

    /**
     * @param $module
     * @return NovaClientPool | null
     */
    public function getPool($module)
    {
        if (!isset($this->novaClientPool[$module])) {
            throw new CanNotFindServiceNamePoolException();
        }
        return $this->novaClientPool[$module];
    }

    public function get($module)
    {
        $pool = $this->getPool($module);
        yield $pool->get();
    }

    public function offline($module, $servers)
    {
        $pool = $this->getPool($module);
        foreach ($servers as $server) {
            $connection = $pool->getConnectionByHostPort($server['host'], $server['port']);
            if (null !== $connection && $connection instanceof Connection) {
                $pool->remove($connection);
            }
        }
    }

    public function addOnline($module, $servers)
    {
        $novaConfig = Config::get('novaConnection');
        $pool = $this->getPool($module);
        foreach ($servers as $server) {
            $novaConfig['host'] = $server['host'];
            $novaConfig['port'] = $server['port'];
            $pool->createConnection($novaConfig);
        }
    }
}