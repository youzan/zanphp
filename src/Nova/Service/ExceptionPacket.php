<?php

namespace Kdt\Iron\Nova\Service;

use Kdt\Iron\Nova\Exception\RpcException;
use Kdt\Iron\Nova\Foundation\Traits\InstanceManager;
use Thrift\Exception\TApplicationException;
use Thrift\Exception\TException as ThriftException;
use Exception as SysException;
use Zan\Framework\Foundation\Exception\ZanException as IronException;

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