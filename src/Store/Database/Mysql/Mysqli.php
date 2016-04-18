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
        if ($result == false) {
            if (in_array($link->errno, [2013, 2006])) {
                $this->connection->close();
                call_user_func($this->callback, false);
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

    }

    /**
     * @return DbResultInterface
     */
    public function commit()
    {

    }

    /**
     * @return DbResultInterface
     */
    public function rollback()
    {

    }
}