<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/16
 * Time: 14:46
 */

namespace Zan\Framework\Store\Facade;


use Zan\Framework\Foundation\Exception\System\InvalidArgument;
use Zan\Framework\Network\Contract\Connection;
use Zan\Framework\Store\Database\Mysql\QueryExecuter;

class Db {
    private $connName = '';
    private $engine = null;
    private $autoHandleException = false;

//    public function __construct(/*String*/$connName)
//    {
//        if(!$connName || !is_string($connName)) {
//            throw new InvalidArgument('invalid connection name for Db.__construct()');
//        }
//
//        $this->connName = $connName;
//        $this->initEngine($connName);
//    }

    public function query($sql)
    {
        return (new QueryExecuter())->query($sql);
    }

    public function beginTransaction($autoHandleException=false)
    {
        $stradegy = (false === $autoHandleException) ? false : true;
        $this->autoHandleException = $stradegy;

        return $this->beginTransaction($stradegy);
    }

    public function commit()
    {
        return $this->engine->commit();
    }

    public function rollback()
    {
        return $this->engine->roolback();
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