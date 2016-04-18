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
use Zan\Framework\Store\Database\Mysql\Mysql;

class MysqlResult implements DbResultInterface
{
    /**
     * @var Mysql
     */
    private $driver;

    /**
     * FutureResult constructor.
     * @param DriverInterface $driver
     */
    public function __construct(DriverInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * @return int
     */
    public function getLastInsertId()
    {
        return $this->driver->getConnection()->getSocket()->insert_id;
    }

    /**
     * @return int
     */
    public function getAffectedRows()
    {
        return $this->driver->getConnection()->getSocket()->affected_rows;
    }

    /**
     * @return array
     */
    public function fetchRows()
    {
        return $this->driver->getResult();
    }
}