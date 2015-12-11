<?php
namespace Zan\Framework\Network\Contract;

use Zan\Framework\Foundation\Contract\PooledObject;

abstract class Connection extends PooledObject
{
    public function isAlive() {
        try {
            $this->ping();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    abstract protected function ping();
    abstract public function connect();
    abstract public function close();
}
