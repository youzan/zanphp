<?php
/**
 * Created by PhpStorm.
 * User: yuzhenfan
 * Date: 16/3/1
 * Time: 下午8:09
 */
namespace Zan\Framework\Store\Database\Mysql;

use Zan\Framework\Network\Connection\ConnectionManager;
use Zan\Framework\Network\Connection\Driver\Mysqli;
use Zan\Framework\Store\Database\Mysql\SqlMap;
use Zan\Framework\Store\Database\Mysql\QueryResult;
use Zan\Framework\Store\Database\Mysql\Exception as MysqlException;
use Zan\Framework\Store\Database\Mysql\Table;
class QueryExecutor
{
    /**
     * @var Mysqli
     */
    private $connection;

    private $sql;
    private $sqlMap;
    private $database;

    public function init($sid, $data, $options)
    {
        $this->initSql($sid, $data, $options);
        $this->initTable();
        yield $this->initConnection();
    }

    private function initSql($sid, $data, $options)
    {
        $sqlMap = SqlMap::getInstance()->getSql($sid, $data, $options);
        $this->sqlMap = $sqlMap;
        $this->sql = $sqlMap['sql'];
    }

    private function initTable()
    {
        $table = $this->sqlMap['table'];
        $this->database = Table::getInstance()->getDatabase($table);
    }

    public function initConnection()
    {
        $key = $this->database . '.' . $this->sqlMap['table'];
        $connectionManager = ConnectionManager::getInstance();
        $this->connection = (yield $connectionManager->get($this->database));
    }

    public function execute()
    {
        $this->onQuery();
        yield new QueryResult($this->connection, $this->sqlMap);
    }

    private function onQuery()
    {
        $connection = $this->connection->getSocket();
//        $this->sql = $connection->real_escape_string($this->sql);
        $result = $connection->query($this->sql, MYSQLI_ASYNC);
        if ($result === false) {
            if (in_array($connection->errno, [2013, 2006])) {
                $this->connection->close();
                throw new MysqlException('数据库链接错误');
            }
            throw new MysqlException($connection->error);
        }
        return true;
    }
}