<?php
namespace Zan\Framework\Network\Contract;

use Zan\Framework\Foundation\Contract\PooledObject;
use Zan\Framework\Foundation\Contract\Resource;
use Zan\Framework\Network\Facade\ConnectionPool;

abstract class Connection extends PooledObject implements Resource
{
    /**
     * @var ConnectionPool
     */
    private $pool = null;

    public function isAlive()
    {
        try {
            $this->ping();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    abstract protected function ping();

    public function setPool(ConnectionPool $pool)
    {
        $this->pool = $pool;
    }

    public function release($strategy = Resource::AUTO_RELEASE)
    {
        if (Resource::RELEASE_AND_DESTROY === $strategy) {
            return $this->close();
        }

        if (null === $this->pool) {
            return $this->close();
        }
        $this->pool->release($this);
    }

    abstract public function close();

    abstract public function connect();
}
