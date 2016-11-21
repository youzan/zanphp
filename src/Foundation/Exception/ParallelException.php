<?php

namespace Zan\Framework\Foundation\Exception;


class ParallelException extends ZanException
{
    /**
     * @var array
     */
    private $parallelResult;

    /**
     * @var \Exception[]
     */
    private $exceptions = [];

    public function __construct($message, $code, \Exception $previous, array $metaData, $parallelResult = null)
    {
        parent::__construct($message, $code, $previous, $metaData);
        $this->parallelResult = $parallelResult;
    }

    public static function makeWithResult($result, array $exceptions)
    {
        $self = new ParallelException("catch an exception in parallel", 0, end($exceptions), [], $result);
        $self->exceptions = $exceptions;
        return $self;
    }

    public function __toString()
    {
        $exString = [ str_pad(" Parallel Exception Start ", 100, "=", STR_PAD_BOTH) ];
        foreach ($this->exceptions as $exception) {
            $exString[] = $exception->__toString();
        }
        $exString[] = str_pad(" Parallel Exception END ", 100, "=", STR_PAD_BOTH);
        return implode("\n\n", $exString);
    }

    public function getParallelResult()
    {
        return $this->parallelResult;
    }

    public function getExceptions()
    {
        return $this->exceptions;
    }
}