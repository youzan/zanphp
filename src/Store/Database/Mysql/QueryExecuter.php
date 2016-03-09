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
class QueryExecuter
{
    /**
     * @var \mysqli
     */
    private $db;

    private $sql;

    private $sqlMap;

    private $callback;

    private $data;

    public function __construct()
    {
        $this->setDb();
    }

    private function setDb()
    {
        if (null == $this->db) {
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
            $this->db = $db;
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

    public function execute($sid, $data, $options)
    {
        $sqlMap = $this->getSqlMap()->getSql($sid, $data, $options);
        $this->sql = $sqlMap['sql'];
        $this->doQuery($this->sql);
        return $this;
    }

    private function insert()
    {
        new \mysqli_stmt($this->db, $this->sql);
    }

    private function query($sql)
    {

    }

    public function send()
    {
//        $this->doQuery($this->sql);
        return $this->onSqlReady();
    }

    private function doQuery($sql)
    {
        $result = $this->db->query($this->sql, MYSQLI_ASYNC);
        if ($result === false) {
            //todo throw error

        }
    }

    public function onSqlReady()
    {
        if ($result = $this->db->reap_async_query()) {

            return $result->fetch_all();
//            if (is_object($result)) {
//                mysqli_free_result($result);
//            }
        } else {
            //todo throw error
        }
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