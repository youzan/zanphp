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
use Zan\Framework\Sdk\Trace\Constant;
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

    private $trace;

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

        $config = $this->connection->getConfig();
        $timeout = isset($config['timeout']) ? $config['timeout'] : self::DEFAULT_QUERY_TIMEOUT;
        $this->sql = $sql;

        $res = swoole_mysql_query($this->connection->getSocket(), $this->sql, [$this, 'onSqlReady']);
        if (false === $res) {
            $this->connection->close();
            throw new MysqliConnectionLostException('Mysql close the connection');
        }
        Timer::after($timeout, [$this, 'onQueryTimeout'], spl_object_hash($this));

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
            if (in_array($link->_errno, [2013, 2006])) {
                $this->connection->close();
                $exception = new MysqliConnectionLostException();
            } elseif ($link->_errno == 1064) {
                $error = $link->_error;
                $this->connection->release();
                $exception = new MysqliSqlSyntaxException($error . ':' . $this->sql);
            } elseif ($link->_errno == 1062) {
                $error = $link->_error;
                $this->connection->release();
                $exception = new MysqliQueryDuplicateEntryUniqueKeyException($error);
            } else {
                $error = $link->_error;
                $this->connection->close();
                $exception = new MysqliQueryException('errno=' . $link->_errno . '&error=' . $error . ':' . $this->sql);
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

    public function onQueryTimeout()
    {
        if ($this->trace) {
            $this->trace->commit("SQL query timeout");
        }
        $this->connection->close();
        //TODO: sql记入日志
        call_user_func_array($this->callback, [null, new MysqliQueryTimeoutException()]);
    }

    public function getResult()
    {
        return $this->result;
    }

    public function beginTransaction()
    {
        $beginTransaction = (yield $this->connection->getSocket()->begin_transaction(MYSQLI_TRANS_START_WITH_CONSISTENT_SNAPSHOT));
        if (!$beginTransaction) {
            throw new MysqliTransactionException('mysqli begin transaction error');
        }
        yield $beginTransaction;
    }

    public function commit()
    {
        $commit = (yield $this->connection->getSocket()->commit());
        if (!$commit) {
            throw new MysqliTransactionException('mysqli commit error');
        }
        $this->connection->release();
        yield $commit;
    }

    public function rollback()
    {
        $rollback = (yield $this->connection->getSocket()->rollback());
        if (!$rollback) {
            throw new MysqliTransactionException('mysqli rollback error');
        }
        $this->connection->release();
        yield $rollback;
    }

    public function releaseConnection()
    {
        $taskId = (yield getTaskId());
        $key = (string)('begin_transaction_' . $taskId);
        $beginTransaction = (yield getContext($key, false));
        if ($beginTransaction === false) {
            $this->connection->release();
        }
        yield true;
    }
}
