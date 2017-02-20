<?php

namespace Zan\Framework\Store\Database\Mysql;

use Zan\Framework\Contract\Store\Database\DriverInterface;
use Zan\Framework\Contract\Network\Connection;
use Zan\Framework\Network\Server\Timer\Timer;
use Zan\Framework\Sdk\Trace\Constant;
use Zan\Framework\Sdk\Trace\Trace;
use Zan\Framework\Store\Database\Mysql\Exception\MysqliConnectionLostException;
use Zan\Framework\Store\Database\Mysql\Exception\MysqliQueryException;
use Zan\Framework\Store\Database\Mysql\Exception\MysqliQueryTimeoutException;
use Zan\Framework\Store\Database\Mysql\Exception\MysqliSqlSyntaxException;
use Zan\Framework\Store\Database\Mysql\Exception\MysqliQueryDuplicateEntryUniqueKeyException;


class Mysql implements DriverInterface
{
    /**
     * @var \Zan\Framework\Network\Connection\Driver\Mysql
     */
    private $connection;

    private $sql;

    /**
     * @var callable
     */
    private $callback;

    private $result;

    /**
     * @var Trace
     */
    private $trace;

    private $countAlias;

    /** @var \swoole_mysql $swooleMysql */
    private $swooleMysql;

    const DEFAULT_QUERY_TIMEOUT = 3000;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->swooleMysql = $connection->getSocket();
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function setCountAlias($countAlias)
    {
        $this->countAlias = $countAlias;
    }

    public function getCountAlias()
    {
        return $this->countAlias;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function execute(callable $callback, $task)
    {
        $this->callback = $callback;
    }

    /**
     * @param $sql
     * @return \Generator
     * @throws MysqliQueryException
     */
    public function query($sql)
    {
        $this->trace = (yield getContext("trace"));
        if ($this->trace) {
            $this->trace->transactionBegin(Constant::SQL, $sql);
        }

        $this->sql = $sql;
        // TODO bind
        $res = $this->swooleMysql->query($this->sql, [], [$this, "onSqlReady"]);
        if (false === $res) {
            throw new MysqliQueryException("query fail");
        }

        $this->beginTimeoutTimer(__FUNCTION__);
        yield $this;
    }

    public function beginTransaction($flags = 0)
    {
        $r = $this->swooleMysql->begin([$this, "onSqlReady"]);
        if ($r === false) {
            throw new MysqliQueryException(__FUNCTION__ . " fail");
        }

        $this->sql = "START TRANSACTION";
        $this->beginTimeoutTimer(__FUNCTION__);
        yield $this;
    }

    public function commit($flags = 0)
    {
        $r = $this->swooleMysql->commit([$this, "onSqlReady"]);
        if ($r === false) {
            throw new MysqliQueryException(__FUNCTION__ . " fail");
        }

        $this->sql = "COMMIT";
        $this->beginTimeoutTimer(__FUNCTION__);
        yield $this;
    }

    public function rollback($flags = 0)
    {
        $r = $this->swooleMysql->rollback([$this, "onSqlReady"]);
        if ($r === false) {
            throw new MysqliQueryException(__FUNCTION__ . " fail");
        }

        $this->sql = "ROLLBACK";
        $this->beginTimeoutTimer(__FUNCTION__);
        yield $this;
    }

    /**
     * @param \swoole_mysql $link
     * @param array|false $result
     * @return void
     */
    public function onSqlReady($link, $result = true)
    {
        $this->cancelTimeoutTimer();

        $exception = null;

        if ($link->errno !== 0 || $result === false) {

            $errno = $link->errno;
            $error = $link->error;
            if (in_array($errno, [2013, 2006])) {
                $exception = new MysqliConnectionLostException("$error:$this->sql");
            } elseif ($errno == 1064) {
                $exception = new MysqliSqlSyntaxException("$error:$this->sql");
            } elseif ($errno == 1062) {
                $exception = new MysqliQueryDuplicateEntryUniqueKeyException("$error:$this->sql");
            } else {
                $exception = new MysqliQueryException("errno=$errno&error=$error:$this->sql", 0, null, ['errno' => $errno, 'error' => $error]);
            }

            if ($this->trace) {
                $this->trace->commit($exception->getTraceAsString());
            }
        } else if ($this->trace) {
            $this->trace->commit(Constant::SUCCESS);
        }

        $this->result = $result;
        call_user_func_array($this->callback, [new MysqliResult($this), $exception]);
    }

    private function beginTimeoutTimer($type)
    {
        $config = $this->connection->getConfig();
        $timeout = isset($config['timeout']) ? $config['timeout'] : self::DEFAULT_QUERY_TIMEOUT;
        Timer::after($timeout, $this->onQueryTimeout($this->sql, $type), spl_object_hash($this));
    }

    private function cancelTimeoutTimer()
    {
        Timer::clearAfterJob(spl_object_hash($this));
    }

    private function onQueryTimeout($sql, $type)
    {
        $start = microtime(true);
        return function() use($sql, $start, $type) {
            if ($this->trace) {
                $this->trace->commit("$type timeout");
            }

            $ctx = [
                "sql" => $sql,
                "duration" => microtime(true) - $start,
            ];
            call_user_func_array($this->callback, [null, new MysqliQueryTimeoutException("Mysql $type timeout", 0, null, $ctx)]);
        };
    }
}
