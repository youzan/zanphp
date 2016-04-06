<?php

/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/5
 * Time: 13:03
 */

namespace Zan\Framework\Network\Connection\Factory;

use Zan\Framework\Contract\Network\ConnectionFactory;

class Mysqli implements ConnectionFactory
{
    /**
     * @var array
     */
    private $config;
    private $conn;

    public function __construct(array $config)
    {
        $this->config = $config;
    }
    
    public function create()
    {
        $this->conn = new \mysqli();
        $this->conn->connect($this->config['host'], $this->config['user'], $this->config['password'],
            $this->config['database'], $this->config['port']);
        $this->conn->autocommit(true);
        return $this->conn;
    }

}