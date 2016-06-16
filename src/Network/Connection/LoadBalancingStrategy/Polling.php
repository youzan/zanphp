<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/5/27
 * Time: 下午7:15
 */
namespace Zan\Framework\Network\Connection\LoadBalancingStrategy;

use Zan\Framework\Contract\Network\Connection;
use Zan\Framework\Contract\Network\LoadBalancingStrategyInterface;
use Zan\Framework\Network\Connection\NovaClientPool;

class Polling implements LoadBalancingStrategyInterface
{
    private $offset;

    private $connectionPool;

    public function __construct(NovaClientPool $connectionPool)
    {
        $this->connectionPool = $connectionPool;
    }

    public function get()
    {
        yield $this->algorithm();
    }

    private function algorithm()
    {
        $connections = $this->connectionPool->getConnections();
        if (count($connections) == 1) {
            $this->offset = key($connections);
            yield reset($connections);
            return;
        }
        if (null === $this->offset) {
            yield reset($connections);
            $this->offset = key(array_slice($connections, 1, 1));
            return;
        }
        if (isset($connections[$this->offset])) {
            yield $connections[$this->offset];
            $keys = array_keys($connections);
            $this->offset = isset($keys[(array_search($this->offset, $keys) + 1)]) ? $keys[(array_search($this->offset, $keys) + 1)] : $keys[0];
            return;
        }
        $this->offset = key($connections);
        yield reset($connections);
        return;
    }
}