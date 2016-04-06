<?php
/**
 * Created by PhpStorm.
 * User: yuzhenfan
 * Date: 16/3/1
 * Time: 下午8:08
 */
namespace Zan\Framework\Store\Database\Mysql;

use Zan\Framework\Foundation\Contract\Async;
use Zan\Framework\Network\Common\Connection;
use Zan\Framework\Store\Database\Mysql\Exception as MysqlException;
use Zan\Framework\Store\Database\Mysql\SqlMap;

class QueryResult implements Async
{
    /**
     * @var Connection
     */
    private $connection;

    private $sqlMap = [];

    private $callback;

    public function __construct($connection, $sqlMap)
    {
        $this->init($connection, $sqlMap);
    }

    private function init($connection, $sqlMap)
    {
        $this->connection = $connection;
        $this->sqlMap = $sqlMap;
        return $this;
    }

    public function execute(callable $callback)
    {
        $this->callback = $callback;
        $dbSock = swoole_get_mysqli_sock($this->connection->getConnection());
        swoole_event_add($dbSock, [$this, 'onQueryReady']);
    }

    public function onQueryReady()
    {
        $dbSock = swoole_get_mysqli_sock($this->connection->getConnection());
        swoole_event_del($dbSock);
        if (null === $this->sqlMap) {
            return false;
        }
        $result = [];
        switch ($this->sqlMap['sql_type']) {
            case 'INSERT' :
                $result =  $this->insert();
                break;
            case 'UPDATE' :
                $result = $this->update();
                break;
            case 'DELETE' :
                $result = $this->delete();
                break;
            case 'SELECT' :
                $result = $this->select();
                break;
        }
        call_user_func($this->callback, $result);
    }

    private function select()
    {
        if ($result = $this->connection->getConnection()->reap_async_query()) {
            $return = [];
            while ($data = $result->fetch_assoc()) {
                $return[] = $data;
            }
            if (is_object($result)) {
                mysqli_free_result($result);
            }
            if ($this->sqlMap['result_type'] == SqlMap::RESULT_TYPE_ROW) {
                return $return[0];
            }
            if ($this->sqlMap['result_type'] == SqlMap::RESULT_TYPE_COUNT) {
                return $return[0]['count_sql_rows'];
            }
            if (in_array($this->sqlMap['result_type'], [SqlMap::RESULT_TYPE_SELECT, SqlMap::RESULT_TYPE_DEFAULT])) {
                return $return;
            }
            return $return;
        } else {
            throw new MysqlException($this->connection->getConnection()->error, $this->connection->getConnection()->errno);
        }
    }

    private function insert()
    {
        $result = $this->connection->getConnection()->reap_async_query();
        if (!$result) {
            throw new MysqlException($this->connection->getConnection()->error, $this->connection->getConnection()->errno);
        }
        if ($this->sqlMap['result_type'] == SqlMap::RESULT_TYPE_INSERT) {
            return $this->connection->getConnection()->insert_id;
        }
        if ($this->sqlMap['result_type'] == SqlMap::RESULT_TYPE_BATCH) {
            return $result;
        }
    }

    private function update()
    {
        $result = $this->connection->getConnection()->reap_async_query();
        if (!$result) {
            throw new MysqlException($this->connection->getConnection()->error, $this->connection->getConnection()->errno);
        }
        if ($this->sqlMap['result_type'] == SqlMap::RESULT_TYPE_UPDATE) {
            return $result ? true : false;
        }
        return $result;
    }

    private function delete()
    {
        $result = $this->connection->getConnection()->reap_async_query();
        if (!$result) {
            throw new MysqlException($this->connection->getConnection()->error, $this->connection->getConnection()->errno);
        }
        if ($this->sqlMap['result_type'] == SqlMap::RESULT_TYPE_DELETE) {
            return $result ? true : false;
        }
        return $result;
    }
}