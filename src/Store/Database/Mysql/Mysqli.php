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

    public function execute(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @param $sql
     * @return DbResultInterface
     */
    public function query($sql)
    {
        $this->sql = $sql;
        swoole_mysql_query($this->connection->getSocket(), $this->sql, [$this, 'onSqlReady']);
        yield $this;
    }

    /**
     * @return DbResultInterface
     */
    public function onSqlReady($link, $result)
    {
        if ($result === false) {
            if (in_array($link->_errno, [2013, 2006])) {
                $this->connection->close();
                throw new MysqliConnectionLostException();
            } elseif ($link->_errno == 1064) {
                $error = $link->_error;
                $this->connection->release();
                throw new MysqliSqlSyntaxException($error);
            } else {
                $error = $link->_error;
                $this->connection->release();
                throw new MysqliQueryException($error);
            }
        }
        $this->result = $result;
        call_user_func($this->callback, new MysqliResult($this));
    }

    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param bool $autoHandleException
     * @return DbResultInterface
     */
    public function beginTransaction()
    {
        $beginTransaction = (yield $this->connection->getSocket()->begin_transaction(MYSQLI_TRANS_START_READ_ONLY));
        if (!$beginTransaction) {
            throw new MysqliTransactionException('mysqli begin transaction error');
        }
        yield $beginTransaction;
    }

    /**
     * @return DbResultInterface
     */
    public function commit()
    {
        $commit = (yield $this->connection->getSocket()->commit());
        if (!$commit) {
            throw new MysqliTransactionException('mysqli commit error');
        }
        yield $commit;
    }

    /**
     * @return DbResultInterface
     */
    public function rollback()
    {
        $rollback = (yield $this->connection->getSocket()->rollback());
        if (!$rollback) {
            throw new MysqliTransactionException('mysqli rollback error');
        }
        yield $rollback;
    }
}
