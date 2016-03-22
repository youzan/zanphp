<?php
/**
 * Network via (?)
 * User: moyo
 * Date: 9/21/15
 * Time: 3:51 PM
 */

namespace Zan\Framework\Network\Tcp\Nova\Network;

use Zan\Framework\Network\Tcp\Nova\Exception\FrameworkException;
use Zan\Framework\Network\Tcp\Nova\Protocol\Packer;
use Zan\Framework\Network\Tcp\Nova\Service\Finder;
use Zan\Framework\Network\Tcp\Nova\Service\Dispatcher;
use Thrift\Exception\TApplicationException;
use Thrift\Type\TMessageType;

abstract class Pipe
{
    /**
     * @var Packer
     */
    private $packer = null;

    /**
     * @var Finder
     */
    private $finder = null;

    /**
     * @var Dispatcher
     */
    private $dispatcher = null;

    /**
     * Via constructor.
     */
    final public function __construct()
    {
        $this->packer = Packer::newInstance();
        $this->finder = Finder::instance();
        $this->dispatcher = Dispatcher::instance();
    }

    /**
     * @param $serviceName
     * @param $methodName
     * @param $inputBIN
     * @return string
     * @throws FrameworkException
     */
    final public function process($serviceName, $methodName, $inputBIN)
    {
        // prepare structs
        $inputStruct = $this->finder->getInputStruct($serviceName, $methodName);
        $outputStruct = $this->finder->getOutputStruct($serviceName, $methodName);
        $exceptionStruct = $this->finder->getExceptionStruct($serviceName, $methodName);
        // checking
        if (is_null($inputStruct) || is_null($outputStruct))
        {
            // maybe method has been removed
            $response = [
                'state' => 'failed',
                'sign' => 'sys-exception',
                'data' => new TApplicationException(
                    is_null($inputStruct) ? 'dispatcher.input.spec.missing' : 'dispatcher.output.spec.missing',
                    TApplicationException::WRONG_METHOD_NAME
                )
            ];
        }
        else
        {
            // decoding
            $inputArguments = $this->packer->decode($inputBIN, $inputStruct);
            // dispatching
            $response = $this->dispatcher->call($serviceName, $methodName, $inputArguments);
        }
        // checking
        if ($response['state'] === 'success')
        {
            // response data
            if ($response['sign'] === 'success')
            {
                $success = $response['data'];
                $exception = null;
            }
            else if ($response['sign'] === 'biz-exception')
            {
                $success = null;
                $exception = $response['data'];
            }
            else
            {
                throw new FrameworkException('dispatcher.response.struct.illegal');
            }
            // encoding
            $package = $this->packer->struct($outputStruct, $exceptionStruct, $success, $exception);
            $outputBIN = $this->packer->encode(TMessageType::REPLY, $methodName, $package);
        }
        else
        {
            // exceptions
            if ($response['state'] === 'failed' && $response['sign'] === 'sys-exception')
            {
                $outputBIN = $this->packer->encode(TMessageType::EXCEPTION, $methodName, $response['data']);
            }
            else
            {
                throw new FrameworkException('dispatcher.response.struct.illegal');
            }
        }
        // over
        return $outputBIN;
    }

    /**
     * @param $serviceName
     * @param $methodName
     * @param $thriftBIN
     * @return string
     */
    abstract public function send($serviceName, $methodName, $thriftBIN);

    /**
     * @return string
     */
    abstract public function recv();
}