<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/16
 * Time: 14:46
 */

namespace Zan\Framework\Store\Facade;


use Zan\Framework\Network\Contract\Connection;

class Db {
    private $connection = null;
    private $engine = null;

    public function __construct(Connection $conn)
    {
        $this->connection = $conn;
        $this->initEngine();
    }

    public function query($sql, $config)
    {
        return $this->engine->query($sql);
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
        return $this->engine->close();
    }

    private function initEngine()
    {
        $this->engine = null;
    }
}