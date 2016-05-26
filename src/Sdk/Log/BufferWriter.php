<?php
/**
 * Created by IntelliJ IDEA.
 * User: nuomi
 * Date: 16/5/26
 * Time: 下午5:17
 */

namespace Zan\Framework\Sdk\Log;


class BufferWrite implements LogWriter
{

    private $bufferSize;
    private $realWriter;

    public function __construct(BaseLogger $logger, $bufferSize)
    {
        if (!$logger) {
            throw new InvalidArgumentException('Logger is required' . $logger);
            return false;
        }
        $this->bufferSize = $bufferSize;
        $this->realWriter = $logger->getWriter();
    }

    public function write($log)
    {
        $this->realWriter->write($log);
    }
}