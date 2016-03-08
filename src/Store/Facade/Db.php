<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/16
 * Time: 14:46
 */

namespace Zan\Framework\Store\Facade;


use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Network\Contract\Connection;
use Zan\Framework\Store\Database\Mysql\FutureQuery;
use Zan\Framework\Store\Database\Mysql\QueryExecuter;

class Db {
    private $connName = '';
    private $engine = null;
    private $autoHandleException = false;

//    public function __construct(/*String*/$connName)
////    {
//        if(!$connName || !is_string($connName)) {
//            throw new InvalidArgumentException('invalid connection name for Db.__construct()');
//        }
//
//        $this->connName = $connName;
//        $this->initEngine($connName);
//    }

    public function query($sql)
    {
        $executer = new QueryExecuter();
        $executer->query($sql);
        yield (new FutureQuery($executer));
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