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
    public static function executer($sid, $data, $options)
    {
        $executer = new QueryExecuter();
        $response = (yield $executer->execute($sid, $data, $options));

        yield $response;

    }
}