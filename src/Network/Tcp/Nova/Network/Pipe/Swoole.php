<?php
/**
 * for swoole (production)
 * User: moyo
 * Date: 9/21/15
 * Time: 3:59 PM
 */

namespace Zan\Framework\Network\Tcp\Nova\Network\Pipe;

use Zan\Framework\Network\Tcp\Nova\Network\Client;
use Zan\Framework\Network\Tcp\Nova\Network\Pipe;

class Swoole extends Pipe
{
    /**
     * @var Client\Swoole
     */
    private $client = null;

    /**
     * @param $serviceName
     * @param $methodName
     * @param $thriftBIN
     * @return bool
     */
    public function send($serviceName, $methodName, $thriftBIN)
    {
        $this->client = Client::instance()->idling();
        $this->client->send($serviceName, $methodName, $thriftBIN);
        return true;
    }

    /**
     * @return string
     */
    public function recv()
    {
        $output = $this->client->recv();
        $this->client = null;
        return $output;
    }
}