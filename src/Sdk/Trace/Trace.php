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
    private $_remoteCallMsgId;
    private $_stack = [];

    public function __construct($config, $rootId = null, $parentId = null)
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

        if ($rootId) {
            $this->_root_id = $rootId;
        }

        if ($parentId) {
            $this->_parent_id = $parentId;
        }

    }

    public function initHeader($msgId = null)
    {
        if (!$this->run) {
            return false;
        }

        if (!$msgId) {
            $msgId = Uuid::get();
        }

        if (!$this->_root_id) {
            $this->_root_id = 'null';
        }

        if (!$this->_parent_id) {
            $this->_parent_id = 'null';
        }
        
        $header = [
            self::PROTOCOL,
            $this->appName,
            $this->hostName,
            $this->ip,
            self::GROUP_NAME,
            $this->pid,
            self::NAME,
            $msgId,
            $this->_parent_id,
            $this->_root_id,
            "null"
        ];
        $this->builder->buildHeader($header);
        var_dump($this->_root_id);
        if (!$this->_root_id) {
            $this->_root_id = $msgId;
        }

        $this->_parent_id = $msgId;
    }

    public function transactionBegin($type, $name)
    {
        if (!$this->run) {
            return false;
        }

        list($usec, $sec) = explode(' ', microtime());
        $time = date("Y-m-d H:i:s", $sec) . substr($usec, 1, 4);

        $trace = [
            "t$time",
            $type,
            $name,
        ];
        $this->builder->buildTransaction($trace);

        $trace[0] = ($sec + $usec) * 100000000;
        array_push($this->_stack, $trace);
    }

    /**
     * @return string
     */
    public function getRootId()
    {
        return $this->_root_id;
    }

    /**
     * @return string
     */
    public function getParentId()
    {
        return $this->_parent_id;
    }

    public function commit($status)
    {
        if (!$this->run) {
            return false;
        }

        list($usec, $sec) = explode(' ', microtime());
        $time = date("Y-m-d H:i:s", $sec) . substr($usec, 1, 4);

        $data = array_pop($this->_stack);
        $utime = (($sec + $usec) * 100000000) - $data[0];
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

    /**
     * @param mixed $remoteCallMsgId
     */
    public function setRemoteCallMsgId($remoteCallMsgId)
    {
        $this->_remoteCallMsgId = $remoteCallMsgId;
    }

    /**
     * @return mixed
     */
    public function getRemoteCallMsgId()
    {
        return $this->_remoteCallMsgId;
    }

    public function send()
    {
        if (!$this->run) {
            yield false;
            return;
        }

        var_dump($this->builder->getData());
//        $connection = (yield ConnectionManager::getInstance()->get("tcp.trace"));
//        $tcpClient = new TcpClient($connection);
//        yield $tcpClient->send($this->builder->getData());
    }
}