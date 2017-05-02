<?php

namespace Zan\Framework\Store\Database\Mysql;

use Zan\Framework\Contract\Store\Database\DriverInterface;
use Zan\Framework\Contract\Network\Connection;
use Zan\Framework\Network\Server\Timer\Timer;
use Zan\Framework\Sdk\Trace\Constant;
use Zan\Framework\Sdk\Trace\DebuggerTrace;
use Zan\Framework\Sdk\Trace\Trace;
use Zan\Framework\Store\Database\Mysql\Exception\MysqliConnectionLostException;
use Zan\Framework\Store\Database\Mysql\Exception\MysqliQueryException;
use Zan\Framework\Store\Database\Mysql\Exception\MysqliQueryTimeoutException;
use Zan\Framework\Store\Database\Mysql\Exception\MysqliSqlSyntaxException;
use Zan\Framework\Store\Database\Mysql\Exception\MysqliQueryDuplicateEntryUniqueKeyException;


class Mysql implements DriverInterface
{
    const QUERY_FAIL = 0;
    const QUERY_ASYNC = 1;
    const QUERY_SYNC = 2;

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

    private $exception;

    /**
     * @var Trace
     */
    private $trace;

    /**
     * @var DebuggerTrace
     */
    private $debuggerTrace;

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

    private function checkResult($queryReturn, $desc)
    {
        // 兼容 swoole2.x 旧版本bool返回值
        // rpm 包 > 2.0.7 之后可以去掉
        if (is_bool($queryReturn)) {
            if ($queryReturn === false) {
                $queryReturn = self::QUERY_FAIL;
            } else if ($this->result === null && $this->exception === null) {
                $queryReturn = self::QUERY_ASYNC;
            } else {
                $queryReturn = self::QUERY_SYNC;
            }
        }

        if ($queryReturn === self::QUERY_FAIL) {
            throw new MysqliQueryException("$desc fail");
        } else if ($queryReturn === self::QUERY_ASYNC) {
            $this->beginTimeoutTimer($desc);
            yield $this;
        } else if ($queryReturn === self::QUERY_SYNC) {
            if ($this->exception) {
                throw $this->exception;
            } else {
                yield new MysqliResult($this);
            }
        }
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

        $debuggerTrace = (yield getContext("debugger_trace"));
        if ($debuggerTrace instanceof DebuggerTrace) {
            $conf = $this->connection->getConfig();
            $dsn = "mysql:host={$conf["host"]};port={$conf["port"]};dbname={$conf["database"]}";
            $debuggerTrace->beginTransaction(Constant::SQL, $sql, $dsn);
            $this->debuggerTrace = $debuggerTrace;
        }

        $this->sql = $sql;
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $r = $this->swooleMysql->query($this->sql, [], [$this, "onSqlReady"]);
        yield $this->checkResult($r, __FUNCTION__);
    }

    public function beginTransaction($flags = 0)
    {
        $this->sql = "START TRANSACTION";
        /** @noinspection PhpUndefinedMethodInspection */
        $r = $this->swooleMysql->begin([$this, "onSqlReady"]);
        yield $this->checkResult($r, __FUNCTION__);
    }

    public function commit($flags = 0)
    {
        $this->sql = "COMMIT";
        /** @noinspection PhpUndefinedMethodInspection */
        $r = $this->swooleMysql->commit([$this, "onSqlReady"]);
        yield $this->checkResult($r, __FUNCTION__);
    }

    public function rollback($flags = 0)
    {
        $this->sql = "ROLLBACK";
        /** @noinspection PhpUndefinedMethodInspection */
        $r = $this->swooleMysql->rollback([$this, "onSqlReady"]);
        yield $this->checkResult($r, __FUNCTION__);
    }

    /**
     * @param \swoole_mysql $link
     * @param array|bool $result
     * @throws MysqliConnectionLostException
     * @throws MysqliQueryDuplicateEntryUniqueKeyException
     * @throws MysqliQueryException
     * @throws MysqliSqlSyntaxException
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
                $ctx = [
                    'sql' => $this->sql,
                    'errno' => $errno,
                    'error' => $error,
                ];
                $exception = new MysqliQueryException("errno=$errno&error=$error:$this->sql", 0, null, $ctx);
            }

            if ($this->trace) {
                $this->trace->commit($exception->getTraceAsString());
            }
            if ($this->debuggerTrace) {
                $this->debuggerTrace->commit("error", $exception);
            }
        } else {
            if ($this->trace) {
                $this->trace->commit(Constant::SUCCESS);
            }
            if ($this->debuggerTrace) {
                $this->debuggerTrace->commit("info", $result);
            }
        }

        $this->result = $result;
        $this->exception = $exception;

        if ($this->callback) {
            call_user_func_array($this->callback, [new MysqliResult($this), $exception]);
            $this->callback = null;
        }
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
            if ($this->debuggerTrace) {
                $this->debuggerTrace->commit("warn", "$type timeout");
            }

            if ($this->callback) {
                $duration = microtime(true) - $start;
                $ctx = [
                    "sql" => $sql,
                    "duration" => $duration,
                ];
                call_user_func_array($this->callback, [null, new MysqliQueryTimeoutException("Mysql $type timeout [sql=$sql, duration=$duration]", 0, null, $ctx)]);
            }
        };
    }
}