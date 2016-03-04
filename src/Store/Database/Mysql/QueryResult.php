<?php
/**
 * Created by PhpStorm.
 * User: yuzhenfan
 * Date: 16/3/1
 * Time: 下午8:08
 */
namespace Zan\Framework\Store\Database\Mysql;

class QueryResult
{
    private $rows = [];

    public function __construct($rows = [])
    {
        $this->setRows($rows);
    }

    private function setRows($rows)
    {
        if (!is_array($rows)) {
            //todo throw
        }
        $this->rows = $rows;
        return $this;
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