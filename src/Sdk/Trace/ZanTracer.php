<?php
namespace Zan\Framework\Sdk\Trace;

use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Core\Env;
use Zan\Framework\Network\Common\TcpClient;
use Zan\Framework\Network\Common\TcpClientEx;
use Zan\Framework\Network\Connection\ConnectionEx;
use Zan\Framework\Network\Connection\ConnectionManager;

class ZanTracer extends Tracer {

    private $appName;
    private $hostName;
    private $ip;
    private $pid;
    private $builder;
    private $_stack = [];

    public function __construct($rootId = null, $parentId = null)
    {
        $this->builder = new TraceBuilder();
        $this->appName = Application::getInstance()->getName();
        $this->hostName = Env::get('hostname');
        $this->ip = Env::get('ip');
        $this->pid = Env::get('pid');

        if ($rootId) {
            $this->root_id = $rootId;
        }

        if ($parentId) {
            $this->parent_id = $parentId;
        }

    }

    public function initHeader($msgId = null)
    {
        if (!$msgId) {
            $msgId = $this->builder->generateId();
        }

        if (!$this->root_id) {
            $this->root_id = 'null';
        }

        if (!$this->parent_id) {
            $this->parent_id = 'null';
        }

        $header = [
            Trace::PROTOCOL,
            $this->appName,
            $this->hostName,
            $this->ip,
            Trace::GROUP_NAME,
            $this->pid,
            Trace::NAME,
            $msgId,
            $this->parent_id,
            $this->root_id,
            "null"
        ];
        $this->builder->buildHeader($header);

        if ($this->root_id === 'null') {
            $this->root_id = $msgId;
        }

        $this->parent_id = $msgId;
    }

    public function transactionBegin($type, $name)
    {
        list($usec, $sec) = explode(' ', microtime());
        $time = date("Y-m-d H:i:s", $sec) . substr($usec, 1, 4);

        $trace = [
            "t$time",
            $type,
            $name,
        ];
        $this->builder->buildTransaction($trace);

        $trace[0] = $sec + $usec;
        array_push($this->_stack, $trace);
    }

    public function transactionEnd($status, $sendData = '')
    {
        list($usec, $sec) = explode(' ', microtime());
        $time = date("Y-m-d H:i:s", $sec) . substr($usec, 1, 4);

        $data = array_pop($this->_stack);
        $utime = floor(($sec + $usec - $data[0]) * 1000000);
        $trace = [
            "T$time",
            $data[1],
            $data[2],
            addslashes($status),
            $utime . "us",
            addslashes($sendData)
        ];
        $this->builder->commitTransaction($trace);
    }

    public function logEvent($type, $status, $name = "", $context = "")
    {
        list($usec, $sec) = explode(' ', microtime());
        $time = date("Y-m-d H:i:s", $sec) . substr($usec, 1, 4);

        $trace = [
            "E$time",
            $type,
            $name,
            $status,
            addslashes($context),
        ];
        $this->builder->buildEvent($trace);
    }

    public function uploadTraceData()
    {
        try {
            $connection = (yield ConnectionManager::getInstance()->get("tcp.trace"));
            if ($connection instanceof ConnectionEx) {
                $tcpClient = new TcpClientEx($connection);
                yield $tcpClient->send($this->builder->getData());
            } else {
                $tcpClient = new TcpClient($connection);
                yield $tcpClient->send($this->builder->getData());
            }
        } catch (\Throwable $t) {
            echo_exception($t);
        } catch (\Exception $e) {
            echo_exception($e);
        }
    }
}