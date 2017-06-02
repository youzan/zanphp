<?php

namespace Zan\Framework\Contract\Store\Database;

interface DbResultInterface
{
    /**
     * FutureResult constructor.
     * @param DriverInterface $driver
     */
    public function __construct(DriverInterface $driver);

    /**
     * @return int
     */
    public function getLastInsertId();

    /**
     * @return int
     */
    public function getAffectedRows();

    /**
     * @return array
     */
    public function fetchRows();
}