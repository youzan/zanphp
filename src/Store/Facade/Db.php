<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/16
 * Time: 14:46
 */

namespace Zan\Framework\Store\Facade;

use Zan\Framework\Store\Database\Mysql\QueryExecutor;

class Db {
    public static function execute($sid, $data, $options = [])
    {
        $executer = new QueryExecutor($sid, $data, $options);
        yield $executer->setConnection();

        $response = (yield $executer->execute($sid, $data, $options));
        yield $response;
    }

}