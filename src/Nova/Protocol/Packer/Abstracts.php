<?php

namespace Kdt\Iron\Nova\Protocol\Packer;

use Kdt\Iron\Nova\Service\ExceptionPacket;
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
     * clear input before DECODE
     */
    final protected function clearInputBuffer()
    {
        $this->inputTrans->flush();
        $this->inputBuffer->available() && $this->inputBuffer->read($this->maxPacketSize);
    }

    /**
     * clear output before ENCODE
     */
    final protected function clearOutputBuffer()
    {
        $this->outputTrans->flush();
        $this->outputBuffer->available() && $this->outputBuffer->read($this->maxPacketSize);
    }

    /**
     * @param $type
     * @param $name
     * @param $args
     * @param $side
     * @return string
     * @throws TProtocolException
     */
    final public function encode($type, $name, $args, $side)
    {
        if ($type == TMessageType::CALL || $type == TMessageType::REPLY)
        {
            return $this->processEncode($type, $name, $args, $side);
        }
        else
        {
            if ($type == TMessageType::EXCEPTION && $args instanceof SysException)
            {
                $exception = ExceptionPacket::instance()->ironInject($args);
                if ($exception instanceof TApplicationException)
                {
                    return $this->processEncode($type, $name, $exception, $side);
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
     * @param $side
     * @return array
     */
    final public function decode($data, $args, $side)
    {
        return $this->processDecode($data, $args, $side);
    }

    /**
     * @param $type
     * @param $name
     * @param $args
     * @param $side
     * @return string
     */
    abstract protected function processEncode($type, $name, $args, $side);

    /**
     * @param $data
     * @param $args
     * @param $side
     * @return array
     */
    abstract protected function processDecode($data, $args, $side);
}