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

class MysqliResult implements DbResultInterface
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
        if (\swoole2x()) {
            $insertId = $this->driver->getConnection()->getSocket()->insert_id;
        } else {
            $insertId = $this->driver->getConnection()->getSocket()->_insert_id;
        }
        yield $insertId;
    }

    /**
     * @return int
     */
    public function getAffectedRows()
    {
        if (\swoole2x()) {
            $affectedRows = $this->driver->getConnection()->getSocket()->affected_rows;
        } else {
            $affectedRows = $this->driver->getConnection()->getSocket()->_affected_rows;
        }
        yield $affectedRows;
    }

    /**
     * @return array
     */
    public function fetchRows()
    {
        yield $this->driver->getResult();
    }

    public function getCountRows()
    {
        $rows = (yield $this->fetchRows());
        $countAlias = $this->driver->getCountAlias();
        yield !isset($rows[0][$countAlias]) ? 0 : (int)$rows[0][$countAlias];
    }
}