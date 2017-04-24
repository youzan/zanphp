<?php
/**
 * Created by IntelliJ IDEA.
 * User: chuxiaofeng
 * Date: 17/3/8
 * Time: 上午11:02
 */

namespace Zan\Framework\Store\Database\Mysql;

use Zan\Framework\Contract\Store\Database\DriverInterface;
use Zan\Framework\Contract\Network\Connection;
use Zan\Framework\Foundation\Core\Debug;
use Zan\Framework\Network\Server\Timer\Timer;
use Zan\Framework\Sdk\Trace\ChromeTrace;
use Zan\Framework\Sdk\Trace\Constant;
use Zan\Framework\Sdk\Trace\Trace;
use Zan\Framework\Store\Database\Mysql\Exception\MysqliConnectionLostException;
use Zan\Framework\Store\Database\Mysql\Exception\MysqliQueryException;
use Zan\Framework\Store\Database\Mysql\Exception\MysqliQueryTimeoutException;
use Zan\Framework\Store\Database\Mysql\Exception\MysqliSqlSyntaxException;
use Zan\Framework\Store\Database\Mysql\Exception\MysqliQueryDuplicateEntryUniqueKeyException;


class Mysql2 implements DriverInterface
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

    /**
     * @var ChromeTrace
     */
    private $chromeTrace;

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

        if (Debug::get()) {
            $chromeTrace = (yield getContext("chrome_trace"));
            if ($chromeTrace instanceof ChromeTrace) {
                $this->chromeTrace = $chromeTrace;
                $chromeTrace->beginTransaction("mysql", $sql);
            }
        }

        $this->sql = $sql;
        $r = $this->swooleMysql->query($this->sql, [$this, "onSqlReady"]);
        if ($r === false) {
            throw new MysqliQueryException("mysql query fail [sql=$this->sql]");
        } else {
            $this->beginTimeoutTimer("mysql query");
            yield $this;
        }
    }

    public function beginTransaction($flags = 0)
    {
        yield $this->query("START TRANSACTION");
    }

    public function commit($flags = 0)
    {
        yield $this->query("COMMIT");
    }

    public function rollback($flags = 0)
    {
        yield $this->query("ROLLBACK");
    }

    /**
     * @param \swoole_mysql $link
     * @param array|bool $result
     * @return void|\Zan\Framework\Contract\Store\Database\DbResultInterface
     * @throws MysqliConnectionLostException
     * @throws MysqliQueryDuplicateEntryUniqueKeyException
     * @throws MysqliQueryException
     * @throws MysqliSqlSyntaxException
     */
    public function onSqlReady($link, $result = true)
    {
        $this->cancelTimeoutTimer();

        $exception = null;

        if ($result === false) {

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
            if ($this->chromeTrace) {
                $this->chromeTrace->commit("error", $exception);
            }
        } else {
            if ($this->trace) {
                $this->trace->commit(Constant::SUCCESS);
            }
            if ($this->chromeTrace) {
                $this->chromeTrace->commit("info", $result);
            }
        }

        $this->result = $result;

        if ($this->callback) {
            $callback = $this->callback;
            $callback(new MysqliResult($this), $exception);
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
            if ($this->chromeTrace) {
                $this->chromeTrace->commit("warn", "$type timeout");
            }

            if ($this->callback) {
                $duration = microtime(true) - $start;
                $ctx = [
                    "sql" => $sql,
                    "duration" => $duration,
                ];
                $callback = $this->callback;
                $ex = new MysqliQueryTimeoutException("Mysql $type timeout [sql=$sql, duration=$duration]", 0, null, $ctx);
                $callback(null, $ex);
            }
        };
    }
}