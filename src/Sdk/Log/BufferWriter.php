<?php

namespace Zan\Framework\Sdk\Log;

use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;

class BufferWriter implements LogWriter
{
    private $bufferSize;
    private $realWriter;

    public function __construct(BaseLogger $logger, $bufferSize)
    {
        if (!$logger) {
            throw new InvalidArgumentException('Logger is required' . $logger);
        }
        $this->bufferSize = $bufferSize;
        $this->realWriter = $logger->getWriter();
    }

    public function write($log)
    {
        yield $this->realWriter->write($log);
    }
}
