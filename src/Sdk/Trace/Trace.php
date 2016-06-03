<?php
/**
 * Created by IntelliJ IDEA.
 * User: Demon
 * Date: 16/5/31
 * Time: 下午4:54
 */

namespace Zan\Framework\Sdk\Trace;

use Zan\Framework\Foundation\Application;
use Zan\Framework\Network\Common\TcpClient;
use Zan\Framework\Network\Connection\ConnectionManager;
use Zan\Framework\Utilities\Encrpt\Uuid;

class Trace
{
    const GROUP_NAME = "zan_group";
    const NAME = "zan";
    const PROTOCOL = "PT1";
    const TRACE_KEY = "CAT_TRACE";

    private $run;
    private $config;
    private $appName;
    private $hostName;
    private $ip;
    private $pid;
    private $builder;

    private $_root_id;
    private $_parent_id;
    private $_stack = [];

    public function __construct($config, $rootId = "null", $parentId = "null")
    {
        $this->run = false;

        if (!$config || !isset($config['run']) || $config['run'] == false) {
            return;
        }
        $this->run = true;

        $this->builder = new TraceBuilder();
        $this->config = $config;
        $this->appName = Application::getInstance()->getName();
        $this->hostName = gethostname();
        $this->ip = gethostbyname($this->hostName);
        $this->pid = getmypid();

        $this->_root_id = $rootId;
        $this->_parent_id = $parentId;
    }

    public function initHeader()
    {
        if (!$this->run) {
            return false;
        }
        $header = [
            self::PROTOCOL,
            $this->appName,
            $this->hostName,
            $this->ip,
            self::GROUP_NAME,
            $this->pid,
            self::NAME,
            Uuid::get(),
            $this->_parent_id,
            $this->_root_id,
            "null"
        ];
        $this->builder->buildHeader($header);
    }

    public function transactionBegin($type, $name)
    {
        if (!$this->run) {
            return false;
        }

        list($usec, $sec) = microtime();
        $time = date("YYYY-MM-DD HH:ii:ss", $sec) . substr($usec, 1, 4);

        $trace = [
            "t$time",
            $type,
            $name,
        ];
        $this->builder->buildTransaction($trace);

        $trace[0] = ($sec + $usec) * 100000000;
        array_push($this->_stack, $trace);
    }

    public function commit($status)
    {
        if (!$this->run) {
            return false;
        }

        list($usec, $sec) = microtime();
        $time = date("YYYY-MM-DD HH:ii:ss", $sec) . substr($usec, 1, 4);

        $data = array_pop($this->_stack);
        $utime = $data[0] - (($sec + $usec) * 100000000);
        $trace = [
            "T$time",
            $data[1],
            $data[2],
            addslashes($status),
            $utime . "us",
        ];
        $this->builder->commitTransaction($trace);
    }

    public function logEvent($type, $status, $name = "", $context = "")
    {
        if (!$this->run) {
            return false;
        }

        list($usec, $sec) = microtime();
        $time = date("YYYY-MM-DD HH:ii:ss", $sec) . substr($usec, 1, 4);

        $trace = [
            "E$time",
            $type,
            $name,
            $status,
            addslashes($context),
        ];
        $this->builder->buildEvent($trace);
    }

    public function send()
    {
        if (!$this->run) {
            return false;
        }

        $connection = (yield ConnectionManager::getInstance()->get("tcp.trace"));
        $tcpClient = new TcpClient($connection);
        yield $tcpClient->send($this->builder->getData());
    }
}