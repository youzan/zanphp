<?php

namespace Zan\Framework\Sdk\Log;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;

abstract class BaseLogger implements LoggerInterface
{
    protected $logMap = [
        'all' => 0,
        'debug' => 1,
        'info' => 2,
        'notice' => 3,
        'warning' => 4,
        'error' => 5,
        'critical' => 6,
        'alert' => 7,
        'emergency' => 8,
    ];
    protected $config;
    protected $writer = null;
    protected $levelNum = 0;

    abstract public function format($level, $message, $context);

    public function init()
    {
    }

    public function __construct(array $config)
    {
        if (!$config) {
            throw new InvalidArgumentException('Config is required' . $config);
        }
        $this->config = $config;
        $this->levelNum = $this->getLevelNum($this->config['level']);
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function emergency($message, array $context = array())
    {
        if ($this->checkLevel(LogLevel::EMERGENCY)) {
            yield $this->write(LogLevel::EMERGENCY, $message, $context);
            return;
        }
        yield null;
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function alert($message, array $context = array())
    {
        if ($this->checkLevel(LogLevel::ALERT)) {
            yield $this->write(LogLevel::ALERT, $message, $context);
            return;
        }
        yield null;
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function critical($message, array $context = array())
    {
        if ($this->checkLevel(LogLevel::CRITICAL)) {
            yield $this->write(LogLevel::CRITICAL, $message, $context);
            return;
        }
        yield null;
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function error($message, array $context = array())
    {
        if ($this->checkLevel(LogLevel::ERROR)) {
            yield $this->write(LogLevel::ERROR, $message, $context);
            return;
        }
        yield null;
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function warning($message, array $context = array())
    {
        if ($this->checkLevel(LogLevel::WARNING)) {
            yield $this->write(LogLevel::WARNING, $message, $context);
            return;
        }
        yield null;
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function notice($message, array $context = array())
    {
        if ($this->checkLevel(LogLevel::NOTICE)) {
            yield $this->write(LogLevel::NOTICE, $message, $context);
            return;
        }
        yield null;
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function info($message, array $context = array())
    {
        if ($this->checkLevel(LogLevel::INFO)) {
            yield $this->write(LogLevel::INFO, $message, $context);
            return;
        }
        yield null;
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function debug($message, array $context = array())
    {
        if ($this->checkLevel(LogLevel::DEBUG)) {
            yield $this->write(LogLevel::DEBUG, $message, $context);
            return;
        }
        yield null;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     * @throws InvalidArgumentException
     */
    public function log($level, $message, array $context = array())
    {
        if (!isset($this->logMap[$level])) {
            throw new InvalidArgumentException('Log level[' . $level . '] is illegal');
        }
        yield $this[$level]($message, $context);
    }

    /**
     * @param $level
     * @return int
     */
    protected function getLevelNum($level)
    {
        return $this->logMap[$level];
    }

    public function checkLevel($level)
    {
        $levelNum = $this->getLevelNum($level);
        if ($levelNum >= $this->levelNum) {
            return true;
        }

        return false;
    }

    /**
     * @return null|FileWriter|SystemWriter|SystemWriterEx|BufferWriter
     */
    public function getWriter()
    {
        return $this->writer;
    }

    public function write($level, $message, array $context = array())
    {
        $log = $this->format($level, $message, $context);
        yield $this->doWrite($log);
    }

    protected function doWrite($log)
    {
        yield $this->getWriter()->write($log);
    }

    protected function formatException(\Exception $e)
    {
        return [
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'stacktraces' => $e->getTraceAsString()
        ];
    }

}
