<?php
/**
 * Created by PhpStorm.
 * User: yuzhenfan
 * Date: 16/3/1
 * Time: 下午8:09
 */
namespace Zan\Framework\Store\Database\Mysql;

use Zan\Framework\Store\Database\Mysql\SqlMap;
use Zan\Framework\Foundation\Core\Event;

class QueryExecuter
{
    /**
     * @var \mysqli
     */
    private $db;

    private $sqlMap;

    public function __construct(\mysqli $db)
    {
        $this->setDb($db);
    }

    private function setDb()
    {
        if (null == $this->db) {
            //todo connectionManage
//            $this->db = new object;
        }
        return $this->db;
    }

    /**
     * @return \mysqli
     */
    public function getDb()
    {
        return $this->db;
    }

    public function execute($sid)
    {

    }

    private function doQuery($sql)
    {
        $mysqli = $this->getDb();
        $result = $mysqli->query($sql, MYSQLI_ASYNC);
        if ($result === false) {
            //todo throw error
        }
        
        yield $this->queryResult($mysqli, $sql);
    }


    private function queryResult()
    {

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

    




}