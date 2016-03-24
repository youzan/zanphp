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
use Zan\Framework\Store\Database\Mysql\Exception as MysqlException;
class QueryExecuter
{
    /**
     * @var \mysqli
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
        $m = new ConnectionManager(null);
        $m->init();
        $db = (yield $m::get('p_zan'));
        $this->connection = $db->getConnection();
    }

    public function execute()
    {
        yield new QueryResult($this->connection, $this->sqlMap);
    }

    private function getSqlMap()
    {
        return new SqlMap();
    }
}