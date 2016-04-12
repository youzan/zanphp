<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/4/12
 * Time: 下午5:04
 */
namespace Zan\Framework\Store\Database\Mysql;

use Zan\Framework\Contract\Store\Database\DbResultInterface;
use Zan\Framework\Contract\Store\Database\DriverInterface;

class DbResult implements DbResultInterface
{
    /**
     * FutureResult constructor.
     * @param DriverInterface $driver
     */
    public function __construct(DriverInterface $driver)
    {

    }

    /**
     * @return int
     */
    public function getLastInsertId()
    {

    }

    /**
     * @return int
     */
    public function getAffectedRows()
    {

    }

    /**
     * @return array
     */
    public function fetchRows()
    {

    }
}