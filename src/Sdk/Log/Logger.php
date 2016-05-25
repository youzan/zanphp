<?php
/**
 * Created by IntelliJ IDEA.
 * User: nuomi
 * Date: 16/5/25
 * Time: 下午4:21
 */

namespace Zan\Framework\Sdk\Log;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class Logger implements LoggerInterface
{
    private $logMap = [
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
    private $logFilter;
    private $logger;
    private $config;
    private $bufferData;

    public function __construct(array $config)
    {
        if (!$config) {
            throw new InvalidArgumentException('Config is required' . $config);
            return false;
        }
        $this->config = $config;
        $this->config['logMap'] = $this->logMap;
        $this->logFilter = new LogFilter($this->config);
        $this->logger = $this->getLogger();
    }

    private function getLogger()
    {
        $logger = null;
        switch ($this->config['factory']) {
            case 'syslog':
                $logger = $this->getSystemLogger();
                break;
            case 'file':
            case 'log':
                $logger = $this->getFileLogger();
                break;
            case 'blackhole':
                $logger = $this->getBlackholeLogger();
                break;
            default:
                throw new InvalidArgumentException('Cannot support this pattern');
        }
        return $logger;
    }

    private function getSystemLogger()
    {
        $logger = new SystemLogger($this->config);
        return $logger;
    }

    private function getFileLogger()
    {
        $logger = new FileLogger($this->config);
        return $logger;
    }

    private function getBlackholeLogger()
    {
        $logger = null;
        return $logger;
    }


    private function write($level, $log)
    {
        $config = $this->config;
        $module = $config['module'];
        $name = $config['name'];
        $this->bufferData .= sprintf("[%s]\t[%s]\t[%s]\t%s\t%s\n", date('Y-m-d H:i:s.u'), $level, $module, $name, $log);
        if (!$config['async'] || !$config['useBuffer'] || (strlen($this->bufferData) >= $config['bufferSize'])) {
            yield $this->logger->write($this->bufferData);
            $this->bufferData = '';
        }
        yield null;
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
        if (!$this->logger) {
            return false;
        }
        $log = $this->logFilter->getFilteredLog(LogLevel::EMERGENCY, $message, $context);
        yield $this->write(LogLevel::EMERGENCY, $log);
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
        // TODO: Implement alert() method.
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
        // TODO: Implement critical() method.
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
        // TODO: Implement error() method.
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
        // TODO: Implement warning() method.
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
        // TODO: Implement notice() method.
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
        // TODO: Implement info() method.
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
        // TODO: Implement debug() method.
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
            return false;
        }
        yield $this[$level]($message, $context);
    }
}
