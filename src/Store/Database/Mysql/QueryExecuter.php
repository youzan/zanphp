<?php
/**
 * Created by PhpStorm.
 * User: yuzhenfan
 * Date: 16/3/1
 * Time: 下午8:09
 */
namespace Zan\Framework\Store\Database\Mysql;

use Zan\Framework\Network\Common\Connection;
use Zan\Framework\Network\Common\ConnectionManager;
use Zan\Framework\Store\Database\Mysql\SqlMap;
use Zan\Framework\Store\Database\Mysql\QueryResult;
use Zan\Framework\Store\Database\Mysql\Exception as MysqlException;
use Zan\Framework\Store\Database\Mysql\Table;
class QueryExecutor
{
    /**
     * @var Connection
     */
    private $connection;

    private $sql;

    private $sqlMap;

    public function __construct($sid, $data, $options)
    {
        $this->init($sid, $data, $options);
    }

    private function init($sid, $data, $options)
    {
        $this->initSql($sid, $data, $options);
        $this->initConnection();
    }

    private function initSql($sid, $data, $options)
    {
        $sqlMap = $this->getSqlMap()->getSql($sid, $data, $options);
        $this->sqlMap = $sqlMap;
        $this->sql = $sqlMap['sql'];
    }

    public function initConnection()
    {
        $table = $this->sqlMap['table'];
        $db = Table::getInstance()->getDatabase($table);
        $key = $db . '.' . $table;
        $connectionManager = new ConnectionManager(null);
        $connectionManager->init();
        $this->connection = (yield $connectionManager->get($key));
    }

    public function execute()
    {
        $this->onQuery();
        yield new QueryResult($this->connection, $this->sqlMap);
    }

    private function onQuery()
    {
        $connection = $this->connection->getConnection();
        $this->sql = $connection->real_escape_string($this->sql);
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

    private function getSqlMap()
    {
        return SqlMap::getInstance();
    }
}