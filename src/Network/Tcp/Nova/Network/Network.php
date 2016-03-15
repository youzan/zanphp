<?php
/**
 * Network transport
 * User: moyo
 * Date: 9/11/15
 * Time: 1:44 PM
 */

namespace Zan\Framework\Network\Tcp\Nova\Network;

use Config;

use Zan\Framework\Network\Tcp\Nova\Foundation\Traits\InstanceManager;
use Zan\Framework\Network\Tcp\Nova\Network\Pipe\Local;
use Zan\Framework\Network\Tcp\Nova\Network\Pipe\Swoole;

class Network
{
    /**
     * Instance mgr
     */
    use InstanceManager;

    /**
     * @var Pipe
     */
    private $pipe = null;

    /**
     * Transport constructor.
     */
    public function __construct()
    {
        if (Config::get('nova_mode') == 'local')
        {
            if (isset($_SERVER['HTTP_VIA_RPC']) && strtolower($_SERVER['HTTP_VIA_RPC']) == 'nova')
            {
                // use swoole (by add http header [Via-RPC => nova])
            }
            else
            {
                $this->pipe = new Local();
            }
        }
        if (is_null($this->pipe))
        {
            $this->pipe = new Swoole();
        }
    }

    /**
     * @param $serviceName
     * @param $methodName
     * @param $thriftBIN
     * @return string
     */
    public function request($serviceName, $methodName, $thriftBIN)
    {
        if ($this->pipe->send($serviceName, $methodName, $thriftBIN))
        {
            return $this->pipe->recv();
        }
        else
        {
            return null;
        }
    }

    /**
     * @param $serviceName
     * @param $methodName
     * @param $thriftBIN
     * @return string
     */
    public function process($serviceName, $methodName, $thriftBIN)
    {
        return $this->pipe->process($serviceName, $methodName, $thriftBIN);
    }
}