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
use Zan\Framework\Store\Database\Mysql\Mysqli;

class MysqliResult implements DbResultInterface
{
    /**
     * @var Mysqli
     */
    private $driver;

    private $countAlias;

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
        $insertId = $this->driver->getConnection()->getSocket()->_insert_id;
        yield $insertId;
    }

    /**
     * @return int
     */
    public function getAffectedRows()
    {
        $affectedRows = $this->driver->getConnection()->getSocket()->_affected_rows;
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