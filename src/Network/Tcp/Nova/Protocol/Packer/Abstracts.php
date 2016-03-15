<?php
/**
 * Packer abstracts
 * User: moyo
 * Date: 10/22/15
 * Time: 3:25 PM
 */

namespace Zan\Framework\Network\Tcp\Nova\Protocol\Packer;

use Zan\Framework\Network\Tcp\Nova\Service\ExceptionPacket;
use Thrift\Exception\TApplicationException;
use Thrift\Exception\TProtocolException;
use Thrift\Protocol\TBinaryProtocolAccelerated;
use Thrift\Transport\TBufferedTransport;
use Thrift\Transport\TMemoryBuffer;
use Thrift\Type\TMessageType;
use Exception as SysException;

abstract class Abstracts
{
    /**
     * @var TBinaryProtocolAccelerated
     */
    protected $inputBin = null;

    /**
     * @var TBinaryProtocolAccelerated
     */
    protected $outputBin = null;

    /**
     * @var TMemoryBuffer
     */
    protected $inputBuffer = null;

    /**
     * @var TMemoryBuffer
     */
    protected $outputBuffer = null;

    /**
     * @var TBufferedTransport
     */
    protected $inputTrans = null;

    /**
     * @var TBufferedTransport
     */
    protected $outputTrans = null;

    /**
     * @var int
     */
    protected $maxPacketSize = 2097152;

    /**
     * @var int
     */
    protected $seqID = 0;

    /**
     * Packer constructor.
     */
    public function __construct()
    {
        // io
        $this->inputBuffer = new TMemoryBuffer();
        $this->inputTrans = new TBufferedTransport($this->inputBuffer);
        $this->inputBin = new TBinaryProtocolAccelerated($this->inputTrans);
        $this->outputBuffer = new TMemoryBuffer();
        $this->outputTrans = new TBufferedTransport($this->outputBuffer);
        $this->outputBin = new TBinaryProtocolAccelerated($this->outputTrans);
        // firing
        $this->constructing();
    }

    /**
     * fire after construct
     */
    protected function constructing()
    {
        // code here
    }

    /**
     * @param $type
     * @param $name
     * @param $args
     * @return string
     * @throws SysException
     */
    final public function encode($type, $name, $args)
    {
        if ($type == TMessageType::CALL || $type == TMessageType::REPLY)
        {
            return $this->processEncode($type, $name, $args);
        }
        else
        {
            if ($type == TMessageType::EXCEPTION && $args instanceof SysException)
            {
                $exception = ExceptionPacket::instance()->ironInject($args);
                if ($exception instanceof TApplicationException)
                {
                    return $this->processEncode($type, $name, $exception);
                }
                else
                {
                    throw $exception;
                }
            }
            else
            {
                throw new TProtocolException('Detect bad msg-type when encoding.');
            }
        }
    }

    /**
     * @param $data
     * @param $args
     * @return array
     * @throws SysException
     */
    final public function decode($data, $args)
    {
        try
        {
            return $this->processDecode($data, $args);
        }
        catch (SysException $e)
        {
            throw ExceptionPacket::instance()->ironExplode($e);
        }
    }

    /**
     * @param $type
     * @param $name
     * @param $args
     * @return string
     */
    abstract protected function processEncode($type, $name, $args);

    /**
     * @param $data
     * @param $args
     * @return array
     * @throws SysException
     */
    abstract protected function processDecode($data, $args);
}