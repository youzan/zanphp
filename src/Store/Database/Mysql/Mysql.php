<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/4/12
 * Time: 下午2:28
 */
namespace Zan\Framework\Store\Database\Mysql;
use Zan\Framework\Contract\Store\Database\DbResultInterface;
use Zan\Framework\Contract\Store\Database\DriverInterface;
use Zan\Framework\Contract\Network\Connection;

class Mysql implements DriverInterface
{
    public function __construct(Connection $conn)
    {

    }

    public function execute(callable $callback)
    {
        // TODO: Implement execute() method.
    }

    /**
     * @param $sql
     * @return DbResultInterface
     */
    public function query($sql)
    {

    }

    /**
     * @param bool $autoHandleException
     * @return DbResultInterface
     */
    public function beginTransaction()
    {

    }

    /**
     * @return DbResultInterface
     */
    public function commit()
    {

    }

    /**
     * @return DbResultInterface
     */
    public function rollback()
    {

    }

    /**
     * @return DbResultInterface
     */
    public function onSqlReady()
    {

    }


}