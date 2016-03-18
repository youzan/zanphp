<?php
/**
 * Created by PhpStorm.
 * User: liuxinlong
 * Date: 16/3/1
 * Time: 21:49
 */

namespace Zan\Framework\Network\Common;


class Connection {

    private $conn;
    private $pool;

    public function __construct($pool)
    {
        $this->conn = new \mysqli();

        $config = array(
            'host' => '192.168.66.202',
            'user' => 'test_koudaitong',
            'password' => 'nPMj9WWpZr4zNmjz',
            'database' => 'zan_test',
            'port' => '3306',
        );
        $this->conn->connect($config['host'], $config['user'], $config['password'], $config['database'], $config['port']);
        $this->conn->autocommit(true);
        $this->pool = $pool;
    }

    public function close() {
        $this->conn->close();
    }

    public function getConnection()
    {
        return $this->conn;
    }

    public function release() {
        if ($this->pool->isExist($this)) {
            $this->pool->release($this);
        } else {
            $this->close();
        }
    }

}