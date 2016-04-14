<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/4/12
 * Time: 下午5:55
 */
namespace Zan\Framework\Store\Database;

use Zan\Framework\Store\Database\Mysql\Mysql;
use Zan\Framework\Store\Database\ResultFormatter;
class Flow
{

    public function query($sid, $data)
    {
        $sql = '';
        $mysql = new Mysql();
        $dbResult = (yield $mysql->query($sql));
        $resultFormatter = new ResultFormatter($dbResult);
        return $resultFormatter->format();
    }



}