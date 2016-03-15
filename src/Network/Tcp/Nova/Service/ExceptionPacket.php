<?php
/**
 * Exception injector
 * User: moyo
 * Date: 10/10/15
 * Time: 6:15 PM
 */

namespace Zan\Framework\Network\Tcp\Nova\Service;

use Zan\Framework\Network\Tcp\Nova\Exception\RpcException;
use Zan\Framework\Network\Tcp\Nova\Foundation\Traits\InstanceManager;
use Thrift\Exception\TApplicationException;
use Thrift\Exception\TException as ThriftException;
use Exception as SysException;
use Exception_Abstract as IronException;

class ExceptionPacket
{
    /**
     * Instance mgr
     */
    use InstanceManager;

    /**
     * @var string
     */
    private $injectTag = 'IRON-E';

    /**
     * @var string
     */
    private $placeTag = '<||>';

    /**
     * @param SysException $e
     * @return SysException
     */
    public function ironInject(SysException $e)
    {
        if ($e instanceof ThriftException)
        {
            return $e;
        }
        else
        {
            return new TApplicationException($e instanceof IronException ? $this->messageInject($e) : $e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param SysException $e
     * @return SysException
     */
    public function ironExplode(SysException $e)
    {
        if ($e instanceof TApplicationException)
        {
            list($exception, $message, $code) = $this->messageExplode($e);
            if ($exception)
            {
                return new $exception($message, $code);
            }
        }
        return new RpcException($e->getMessage(), $e->getCode());
    }

    /**
     * @param SysException $e
     * @return string
     */
    private function messageInject(SysException $e)
    {
        return sprintf('<%s[%s]>%s%s', $this->injectTag, get_class($e), $this->placeTag, $e->getMessage());
    }

    /**
     * @param SysException $e
     * @return array
     */
    private function messageExplode(SysException $e)
    {
        $message = $e->getMessage();
        $code = $e->getCode();
        // pos
        $headSign = '<'.$this->injectTag.'[';
        $headLen = strlen($headSign);
        $headStart = strpos($message, $headSign);
        $footSign = ']>';
        $footLen = strlen($footSign);
        $footStart = strpos($message, $footSign);
        if (is_numeric($headStart) && is_numeric($footStart))
        {
            // cut
            $exception = substr($message, $headStart + $headLen, $footStart - $headStart - $headLen);
            $message = substr($message, $footStart + $footLen + strlen($this->placeTag));
            // over
            return [$exception, $message, $code];
        }
        else
        {
            return [null, $message, $code];
        }
    }
}