<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/6/3
 * Time: 下午9:30
 */
namespace Zan\Framework\Store\Database\Mysql\Exception;


use Zan\Framework\Network\Connection\Exception\ConnectionLostException;

class MysqliQueryDuplicateEntryUniqueKeyException extends ConnectionLostException
{

}