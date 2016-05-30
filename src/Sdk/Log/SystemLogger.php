<?php
/**
 * Created by IntelliJ IDEA.
 * User: nuomi
 * Date: 16/5/24
 * Time: 下午2:51
 */

namespace Zan\Framework\Sdk\Log;

use Zan\Framework\Foundation\Core\Env;
use Zan\Framework\Network\Connection\ConnectionManager;

class SystemLogger extends BaseLogger
{

    private $priority;
    private $hostname;
    private $server;
    private $pid;
    private $conn = null;
    private $connectionConfig;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->priority = LOG_LOCAL3 + LOG_INFO;
        $this->hostname = Env::get('hostname');
        $this->server = $this->hostname . "/" . gethostbyname($this->hostname);
        $this->pid = Env::get('pid');
        $this->connectionConfig = 'syslog.' . str_replace('/', '', $this->conn['path']);
    }

    public function init()
    {
        $this->conn = (yield ConnectionManager::getInstance()->get($this->connectionConfig));
        $this->writer = new SystemWriter($this->conn);
        yield $this->writer->init();
    }

    public function format($level, $message, $context)
    {
        $header = $this->buildHeader($level);
        $topic = $this->buildTopic();
        $module = $this->config['module'];
        $body = $this->buildBody();
        $result = $header . "topic=" . $topic . " " . $module . " " . $body;

        return $result;
    }

    protected function doWrite($log)
    {
        if (!$this->writer || !$this->conn instanceof Syslog) {
            yield $this->init();
        }
        yield $this->getWriter()->write($log);
    }

    private function buildHeader($level)
    {
        $time = date("Y-m-d H:i:s");
        return "<{$this->priority}>{$time} {$this->server} {$level}[{$this->pid}]: ";
    }

    private function buildTopic()
    {
        return 'test topic';
    }

    private function buildBody()
    {
        return 'test body';
    }

}
