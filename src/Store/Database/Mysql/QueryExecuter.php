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

    public function execute($sid)
    {

    }

    public function query($sql)
    {
        $this->sql = $sql;
        return $this;
    }

    public function send()
    {
//        $db_sock = swoole_get_mysqli_sock($this->db);
//        swoole_event_add($db_sock, [$this, 'onSqlReady']);
        $this->doQuery($this->sql);
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