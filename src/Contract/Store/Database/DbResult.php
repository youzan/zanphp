<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/5
 * Time: 21:08
 */

namespace Zan\Framework\Contract\Store\Database;

interface DbResult
{
    /**
     * FutureResult constructor.
     * @param Driver $driver
     */
    public function __construct(Driver $driver);

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