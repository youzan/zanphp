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

    public function work($serviceName, $servers)
    {
        $config = Config::get('loadBalancing');
        $novaConfig = Config::get('connection.nova');
        foreach ($servers as $server) {
            $novaConfig['host'] = $server['host'];
            $novaConfig['port'] = $server['port'];
            $config['connections'][] = $novaConfig;
        }
        $this->novaClientPool[$serviceName] = new NovaClientPool($config);
    }

    /**
     * @param $serviceName
     * @return NovaClientPool | null
     */
    public function getPool($serviceName)
    {
        if (!isset($this->novaClientPool[$serviceName])) {
            throw new CanNotFindServiceNamePoolException();
        }
        return $this->novaClientPool[$serviceName];
    }

    public function get($serviceName)
    {
        $pool = $this->getPool($serviceName);
        yield $pool->get();
    }

    public function offline($serviceName, $servers)
    {
        $pool = $this->getPool($serviceName);
        foreach ($servers as $server) {
            $connection = $pool->getConnectionByHostPort($server['host'], $server['port']);
            if (null !== $connection && $connection instanceof Connection) {
                $pool->remove($connection);
            }
        }
    }

    public function addOnline($serviceName, $servers)
    {
        $novaConfig = Config::get('novaConnection');
        $pool = $this->getPool($serviceName);
        foreach ($servers as $server) {
            $novaConfig['host'] = $server['host'];
            $novaConfig['port'] = $server['port'];
            $pool->createConnection($novaConfig);
        }
    }
}