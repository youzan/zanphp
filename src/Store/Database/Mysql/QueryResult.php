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

    private $rows = [];

    private $insertId;

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
            if (in_array($this->sqlMap['result_type'], [SqlMap::RESULT_TYPE_ALL, SqlMap::RESULT_TYPE_DEFAULT])) {
                return $return;
            }
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
        return $this->connection->getConnection()->insert_id;
    }

    private function update()
    {
        $result = $this->connection->getConnection()->reap_async_query();
        if (!$result) {
            throw new MysqlException($this->connection->getConnection()->error, $this->connection->getConnection()->errno);
        }
        return $result;
    }

    private function delete()
    {
        $result = $this->connection->getConnection()->reap_async_query();
        if (!$result) {
            throw new MysqlException($this->connection->getConnection()->error, $this->connection->getConnection()->errno);
        }
        return $result;
    }




    private function setRows($rows)
    {
        if (!is_array($rows)) {
            //todo throw
        }
        $this->rows = $rows;
        return $this;
    }

    private function setInsertId($insertId)
    {
        $this->insertId = $insertId;
        return $this;
    }

    public function getInsertId()
    {
        return $this->insertId;
    }

    public function one()
    {
        if ([] === $this->rows) {
            return null;
        }
        return $this->rows[0];
    }

    public function all()
    {
        return $this->rows;
    }

    public function count($q = '*')
    {
        if (!isset($this->rows[0]) || !isset($this->rows[0]['count('.$q.')'])) {
            return 0;
        }
        return $this->rows[0]['count('.$q.')'];
    }

    public function exits()
    {
        if (!isset($this->rows[0]) || count($this->rows[0]) == 0) {
            return false;
        }
        return true;
    }


}