<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/16
 * Time: 15:29
 */

namespace Zan\Framework\Store\Database;


use Zan\Framework\Store\Database\Contract\Engine;
use Zan\Framework\Network\Contract\Connection;

class Mysql implements Engine {
    private $connection = null;

    public function __construct(Connection $conn)
    {
        $this->connection = $conn;
    }

    public function query($sql, array $config=null)
    {
        try {
        } catch (\Exception $e) {

        }
    }

    public function beginTransaction($autoHandleException=false)
    {

    }

    public function commit()
    {

    }

    public function rollback()
    {

    }

    public function close()
    {
        return release($this->connection);
    }

}