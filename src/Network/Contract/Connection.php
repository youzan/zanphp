<?php
namespace Zan\Framework\Network\Contract;

use Zan\Framework\Foundation\Contractst\PooledObject;
use Zan\Framework\Foundation\Contract\Resource;
use Zan\Framework\Network\Facade\ConnectionPool;

abstract class Connection extends PooledObject implements Resource
{
    private $pool = null;

    public function isAlive() {
        try {
            $this->ping();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    public function setPool(ConnectionPool $pool)
    {
        $this->pool = $pool;
    }

    public function release($stradegy=Resource::AUTO_RELEASE)
    {
        if(Resource::RLEASE_AND_DESTROY === $stradegy) {
            return $this->close();
        }

        if(null === $this->pool){
            return $this->close();
        }
        $this->pool->release($this);
    }

    abstract protected function ping();
    abstract public function connect();
    abstract public function close();
}
