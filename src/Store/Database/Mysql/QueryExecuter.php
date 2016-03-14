<?php
/**
 * Created by PhpStorm.
 * User: yuzhenfan
 * Date: 16/3/1
 * Time: 下午8:09
 */
namespace Zan\Framework\Store\Database\Mysql;

use Zan\Framework\Store\Database\Mysql\SqlMap;
use Zan\Framework\Store\Database\Mysql\FutureQuery;
use Zan\Framework\Store\Database\Mysql\QueryResult;
class QueryExecuter
{
    /**
     * @var \mysqli
     */
    private $connection;

    private $sql;

    private $sqlMap;

    public function __construct()
    {
        $this->setConnection();
    }

    private function setConnection()
    {
        if (null == $this->connection) {
            //todo connectionManage
            $db = new \mysqli();
            $config = array(
                'host' => '127.0.0.1',
                'user' => 'root',
                'password' => '',
                'database' => 'test',
                'port' => '3306',
            );
            $db->connect($config['host'], $config['user'], $config['password'], $config['database'], $config['port']);
            $db->autocommit(true);
            $this->connection = $db;
        }
        return $this->connection;
    }

    /**
     * @return \mysqli
     */
    public function getConnection()
    {
        return $this->connection;
    }

    public function execute($sid, $data, $options)
    {
        $sqlMap = $this->getSqlMap()->getSql($sid, $data, $options);
        $this->sql = $sqlMap['sql'];
        $this->doQuery();
        $queryResult = $this->queryResult($sqlMap);
        $response = (yield $queryResult);
        yield $response;
    }

    private function doQuery()
    {
        $result = $this->connection->query($this->sql, MYSQLI_ASYNC);
        if ($result === false) {
            //todo throw error
        }
    }

    private function getSqlMap()
    {
        if (null == $this->sqlMap) {
            $this->sqlMap = $this->createSqlMap();
        }
        return $this->sqlMap;
    }

    private function createSqlMap()
    {
        return new SqlMap();
    }

    private function queryResult($sqlMap)
    {
        return new QueryResult($this->connection, $sqlMap);
    }
}