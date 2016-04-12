<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/5
 * Time: 21:08
 */

namespace Zan\Framework\Contract\Store\Database;
use Zan\Framework\Contract\Store\Database\DriverInterface;
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