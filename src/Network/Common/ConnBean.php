<?php
/**
 * Created by PhpStorm.
 * User: liuxinlong
 * Date: 16/3/1
 * Time: 21:49
 */

namespace Zan\Framework\Network\Common;


class ConnBean {

    public $db;

    public function __construct()
    {
        $this->db = new \mysqli();

        $config = array(
            'host' => '192.168.66.202',
            'user' => 'test_koudaitong',
            'password' => 'nPMj9WWpZr4zNmjz',
            'database' => 'zan_test',
            'port' => '3306',
        );
        $this->db->connect($config['host'], $config['user'], $config['password'], $config['database'], $config['port']);
        $this->db->autocommit(true);
    }

}