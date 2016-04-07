<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/4/5
 * Time: 20:12
 */

namespace Zan\Framework\Contract\Store\Database;

use Zan\Framework\Contract\Network\Connection;
use Zan\Framework\Foundation\Contract\Async;

interface Driver extends Async
{
    public function __construct(Connection $conn);

    /**
     * @param $sql
     * @return DbResult
     */
    public function query($sql);

    /**
     * @param bool $autoHandleException
     * @return DbResult
     */
    public function beginTransaction();

    /**
     * @return DbResult
     */
    public function commit();

    /**
     * @return DbResult
     */
    public function rollback();

    /**
     * @return DbResult
     */
    public function onSqlReady(); 
}