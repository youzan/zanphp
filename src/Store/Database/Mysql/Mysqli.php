<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/4/12
 * Time: 下午2:28
 */
namespace Zan\Framework\Store\Database\Mysql;
use Zan\Framework\Contract\Store\Database\DbResultInterface;
use Zan\Framework\Contract\Store\Database\DriverInterface;
use Zan\Framework\Contract\Network\Connection;
use Zan\Framework\Network\Server\Timer\Timer;
use Zan\Framework\Sdk\Trace\ChromeTrace;
use Zan\Framework\Sdk\Trace\Constant;
use Zan\Framework\Sdk\Trace\Trace;
use Zan\Framework\Store\Database\Mysql\Exception\MysqliConnectionLostException;
use Zan\Framework\Store\Database\Mysql\Exception\MysqliQueryException;
use Zan\Framework\Store\Database\Mysql\Exception\MysqliQueryTimeoutException;
use Zan\Framework\Store\Database\Mysql\Exception\MysqliSqlSyntaxException;
use Zan\Framework\Store\Database\Mysql\Exception\MysqliTransactionException;
use Zan\Framework\Store\Database\Mysql\Exception\MysqliQueryDuplicateEntryUniqueKeyException;

class Mysqli implements DriverInterface
{
    /**
     * @var Connection
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

    const DEFAULT_QUERY_TIMEOUT = 3000;

    public function __construct(Connection $connection)
    {
        $this->setConnection($connection);
    }

    private function setConnection(Connection $connection)
    {
        $this->connection = $connection;
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

    public function execute(callable $callback, $task)
    {
        $this->callback = $callback;
    }

    /**
     * @param $sql
     * @return DbResultInterface
     */
    public function query($sql)
    {
        $this->trace = (yield getContext('trace'));
        if ($this->trace) {
            $this->trace->transactionBegin(Constant::SQL, $sql);
        }

        $chromeTrace = (yield getContext("chrome_trace"));
        if ($chromeTrace instanceof ChromeTrace) {
            $this->chromeTrace = $chromeTrace;
            $chromeTrace->beginTransaction("mysql", $sql);
        }

        $config = $this->connection->getConfig();
        $timeout = isset($config['timeout']) ? $config['timeout'] : self::DEFAULT_QUERY_TIMEOUT;
        $this->sql = $sql;

        $res = swoole_mysql_query($this->connection->getSocket(), $this->sql, [$this, 'onSqlReady']);
        if (false === $res) {
            throw new MysqliConnectionLostException('Mysql close the connection');
        }
        Timer::after($timeout, $this->onQueryTimeout($this->sql), spl_object_hash($this));

        yield $this;
    }

    /**
     * @return DbResultInterface
     */
    public function onSqlReady($link, $result)
    {
        Timer::clearAfterJob(spl_object_hash($this));
        $exception = null;
        if ($result === false) {
            if (property_exists($link, "_errno") && property_exists($link, "_error")) {
                $errno = $link->_errno;
                $error = $link->_error;
                if (in_array($errno, [2013, 2006])) {
                    $exception = new MysqliConnectionLostException("$error:$this->sql");
                } elseif ($errno == 1064) {
                    $exception = new MysqliSqlSyntaxException("$error:$this->sql");
                } elseif ($errno == 1062) {
                    $exception = new MysqliQueryDuplicateEntryUniqueKeyException("$error:$this->sql");
                } else {
                    $exception = new MysqliQueryException('errno=' . $errno . '&error=' . $error . ':' . $this->sql, 0, null, ['errno' => $errno, 'error' => $error]);
                }
            } else {
                $exception = new MysqliConnectionLostException("mysql connection lost: $this->sql");
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
        call_user_func_array($this->callback, [new MysqliResult($this), $exception]);
    }

    public function onQueryTimeout($sql)
    {
        $start = microtime(true);
        return function() use($sql, $start) {
            if ($this->trace) {
                $this->trace->commit("SQL query timeout");
            }
            if ($this->chromeTrace) {
                $this->chromeTrace->commit("warn", "SQL query timeout");
            }

            $ctx = [
                "sql" => $sql,
                "duration" => microtime(true) - $start,
            ];
            call_user_func_array($this->callback, [null, new MysqliQueryTimeoutException('Mysql query timeout', 0, null, $ctx)]);
        };
    }

    public function getResult()
    {
        return $this->result;
    }

    public function beginTransaction($flags = 0)
    {
        $conn = $this->connection->getSocket();
        yield $this->begin_transaction($conn, $flags);

        /*
        $beginTransaction = (yield $this->connection->getSocket()->begin_transaction(MYSQLI_TRANS_START_WITH_CONSISTENT_SNAPSHOT));
        if (!$beginTransaction) {
            throw new MysqliTransactionException('mysqli begin transaction error');
        }
        yield $beginTransaction;
        */
    }

    public function commit($flags = 0)
    {
        $conn = $this->connection->getSocket();
        yield $this->commit_or_rollback($conn, true, $flags);
    }

    public function rollback($flags = 0)
    {
        $conn = $this->connection->getSocket();
        yield $this->commit_or_rollback($conn, false, $flags);
    }

    private function begin_transaction(\mysqli $conn, $flags = 0)
    {
        $characteristic = [];
        if ($flags & MYSQLI_TRANS_START_WITH_CONSISTENT_SNAPSHOT) {
            $characteristic[] = "WITH CONSISTENT SNAPSHOT";
        }
        if ($flags & (MYSQLI_TRANS_START_READ_ONLY | MYSQLI_TRANS_START_READ_WRITE)) {
            if ($conn->server_version < 50605) {
                trigger_error(E_USER_WARNING, "This server version doesn't support 'READ WRITE' and 'READ ONLY'. Minimum 5.6.5 is required");
            } else if ($flags & MYSQLI_TRANS_START_READ_WRITE) {
                $characteristic[] = "READ WRITE";
            } else if ($flags & MYSQLI_TRANS_START_READ_ONLY) {
                $characteristic[] = "READ ONLY";
            }
        }

        $query = "START TRANSACTION " . implode(", ", $characteristic);
        yield $this->query($query);
    }

    private function commit_or_rollback(\mysqli $conn, $commit, $flags = 0)
    {
        $ops = [];
        if ($flags & MYSQLI_TRANS_COR_AND_CHAIN && !($flags & MYSQLI_TRANS_COR_AND_NO_CHAIN)) {
            $ops[] = "AND CHAIN";
        } else if ($flags & MYSQLI_TRANS_COR_AND_NO_CHAIN && !($flags & MYSQLI_TRANS_COR_AND_CHAIN)) {
            $ops[] = "AND NO CHAIN";
        }

        if ($flags & MYSQLI_TRANS_COR_RELEASE && !($flags & MYSQLI_TRANS_COR_NO_RELEASE)) {
            $ops[] = "RELEASE";
        } else if ($flags & MYSQLI_TRANS_COR_NO_RELEASE && !($flags & MYSQLI_TRANS_COR_RELEASE)) {
            $ops[] = "NO RELEASE";
        }

        $query = ($commit ? "COMMIT " : "ROLLBACK ") . implode(" ", $ops);
        yield $this->query($query);
    }
}
