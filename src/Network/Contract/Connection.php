<?php
/*
 *    Copyright 2012-2016 Youzan, Inc.
 *
 *    Licensed under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an "AS IS" BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
 */
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
