<?php
/**
 * Client manager
 * User: moyo
 * Date: 9/28/15
 * Time: 3:16 PM
 */

namespace Zan\Framework\Network\Tcp\Nova\Network;

use Zan\Framework\Network\Tcp\Nova\Foundation\Traits\InstanceManager;
use Zan\Framework\Network\Tcp\Nova\Network\Client\Swoole;

class Client
{
    /**
     * Instance mgr
     */
    use InstanceManager;

    /**
     * @var Swoole[]
     */
    private $pool = [];

    /**
     * @return Swoole
     */
    public function idling()
    {
        shuffle($this->pool);
        foreach ($this->pool as $connect)
        {
            if ($connect->idle())
            {
                return $connect;
            }
        }
        return $this->create();
    }

    /**
     * @return Swoole
     */
    private function create()
    {
        $this->pool[] = $client = new Swoole();
        return $client;
    }
}