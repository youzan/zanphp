<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/4/12
 * Time: 下午5:55
 */
namespace Zan\Framework\Store\Database;

use Zan\Framework\Store\Database\Mysql\Mysql;
use Zan\Framework\Store\Database\Sql\SqlMap;
use Zan\Framework\Store\Database\Sql\Table;
use Zan\Framework\Network\Connection\ConnectionManager;

class Flow
{
    public function query($sid, $data, $options)
    {
        $sqlMap = SqlMap::getInstance()->getSql($sid, $data, $options);
        $database = Table::getInstance()->getDatabase($sqlMap['table']);
        $connection = (yield ConnectionManager::getInstance()->get($database));
        $mysql = new Mysql($connection);
        $dbResult = (yield $mysql->query($sqlMap['sql']));
        if (false === $dbResult) {
            $connection = (yield ConnectionManager::getInstance()->get($database));
            $mysql = new Mysql($connection);
            $dbResult = (yield $mysql->query($sqlMap['sql']));
        }
        $resultFormatter = new ResultFormatter($dbResult, $sqlMap['result_type']);
        yield $resultFormatter->format();
        return;
    }
}