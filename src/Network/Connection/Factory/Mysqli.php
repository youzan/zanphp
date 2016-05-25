<?php
/*
 *    Copyright 2012-2016 Youzan, Inc.
 *
 *    Licensed under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an "AS IS" BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
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

        $connection = new \Zan\Framework\Network\Connection\Driver\Mysqli();
        $connection->setSocket($this->conn);
        $connection->setConfig($this->config);
        return $connection;
    }

    public function close()
    {
        mysqli_close($this->conn);
    }

}