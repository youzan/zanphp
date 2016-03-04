<?php
/**
 * Created by PhpStorm.
 * User: yuzhenfan
 * Date: 16/3/1
 * Time: 下午8:09
 */
namespace Zan\Framework\Store\Database\Mysql;

use Zan\Framework\Store\Database\Mysql\SqlMap;

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
            //todo
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

    public function insert()
    {
//        $sql = $this->getSqlMap()->getSql();

    }

    public function update()
    {

    }

    public function delete()
    {

    }

    public function query($sql)
    {
//        $link1 = mysqli_connect();
        $this->getDb()->query("$sql", MYSQLI_ASYNC);
        $allLinks = [$this->db];
        $processed = 0;
        do {
            $links = $errors = $reject = [];
            foreach ($allLinks as $link) {
                $links[] = $errors[] = $reject[] = $link;
            }
            if (!$this->getDb()->poll($links, $errors, $reject, 1)) {
                continue;
            }
            foreach ($links as $link) {
                if ($result = $link->reap_async_query()) {
                    print_r($result->fetch_row());
                    if (is_object($result))
                        mysqli_free_result($result);
                } else die(sprintf("MySQLi Error: %s", mysqli_error($link)));
                $processed++;
            }
        } while ($processed < count($allLinks));
    }




    private function validate()
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