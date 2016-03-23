<?php
/**
 * Created by PhpStorm.
 * User: yuzhenfan
 * Date: 16/3/1
 * Time: 下午8:09
 */
namespace Zan\Framework\Store\Database\Mysql;

use Zan\Framework\Network\Common\ConnectionManager;
use Zan\Framework\Store\Database\Mysql\SqlMap;
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

    }

    public function setConnection()
    {
        if (null == $this->connection) {
            $m = new ConnectionManager(null);

            $db = (yield $m::get('p_zan'));
            $this->connection = $db->getConnection();
        }
    }

    /**
     * @return \mysqli
     */
    public function getConnection()
    {
        return $this->connection;
    }

    public function execute($sid, $data, $options = [])
    {
        $sqlMap = $this->getSqlMap()->getSql($sid, $data, $options);
        $this->sql = $sqlMap['sql'];
        //$this->doQuery();
        yield $this->queryResult($sqlMap);
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