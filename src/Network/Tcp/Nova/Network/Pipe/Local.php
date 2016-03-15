<?php
/**
 * for local (testing)
 * User: moyo
 * Date: 9/21/15
 * Time: 3:43 PM
 */

namespace Zan\Framework\Network\Tcp\Nova\Network\Pipe;

use Zan\Framework\Network\Tcp\Nova\Network\Pipe;

class Local extends Pipe
{
    /**
     * @var string
     */
    private $buffer = '';

    /**
     * @param $serviceName
     * @param $methodName
     * @param $thriftBIN
     * @return bool
     */
    public function send($serviceName, $methodName, $thriftBIN)
    {
        $this->buffer = $this->process($serviceName, $methodName, $thriftBIN);
        return true;
    }

    /**
     * @return string
     */
    public function recv()
    {
        $output = $this->buffer;
        $this->buffer = '';
        return $output;
    }
}