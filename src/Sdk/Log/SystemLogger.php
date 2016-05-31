<?php
/**
 * Created by IntelliJ IDEA.
 * User: nuomi
 * Date: 16/5/24
 * Time: 下午2:51
 */

namespace Zan\Framework\Sdk\Log;

use Zan\Framework\Foundation\Core\Env;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Network\Connection\ConnectionManager;
use Zan\Framework\Network\Connection\Factory\Syslog;

class SystemLogger extends BaseLogger
{
    const TOPIC_PREFIX = 'track';
    private $priority;
    private $hostname;
    private $server;
    private $pid;
    private $conn = null;
    private $connectionConfig;
    private $supportStoreType = [
        'normal' => 'normal',
        'persistence' => 'persistence'
    ];

    public function __construct($config)
    {
        parent::__construct($config);
        
        if (!isset($this->supportStoreType[$this->config['storeType']])) {
            throw new InvalidArgumentException('StoreType is invalid' . $this->config['storeType']);
        }
        
        $this->connectionConfig = 'syslog.' . str_replace('/', '', $this->config['path']);
        $this->priority = LOG_LOCAL3 + LOG_INFO;
        $this->hostname = Env::get('hostname');
        $this->server = $this->hostname . '/' . gethostbyname($this->hostname);
        $this->pid = Env::get('pid');
    }

    public function init()
    {
        $this->conn = (yield ConnectionManager::getInstance()->get($this->connectionConfig));
        $this->writer = new SystemWriter($this->conn);
    }

    public function format($level, $message, $context)
    {
        $header = $this->buildHeader($level);
        $topic = $this->buildTopic();
        $module = $this->config['module'];
        $body = $this->buildBody($level, $message, $context);
        $result = $header . 'topic=' . $topic . ' ' . $module . ' ' . $body;

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
        $time = date('Y-m-d H:i:s');
        return "<{$this->priority}>{$time} {$this->server} {$level}[{$this->pid}]: ";
    }

    private function buildTopic()
    {
        $config = $this->config;
        $result = SystemLogger::TOPIC_PREFIX . '.' . $config['storeType'];
        if (isset($config['module'])) {
            $result = $result . '.' . $config['module'];
        }
        return $result;
    }

    private function buildBody($level, $message, array $context = [])
    {
        $detail = [];
        if (isset($context['exception']) 
                && $context['exception'] instanceof \Exception) {
            $detail['error'] = $this->formatException($context['exception']);
            unset($context['exception']);
        }
        
        $detail['extra'] = $context;
        $result = [
            'platform' => 'php',
            'app' => $this->config['app'],
            'module' => $this->config['module'],
            'type' => '',
            'level' => $level,
            'tag' => $message,
            'detail' => $detail
        ];
        
        return json_encode($result);
    }

}
