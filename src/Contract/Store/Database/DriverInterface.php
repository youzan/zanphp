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

interface DriverInterface extends Async
{
    public function __construct(Connection $conn);

    /**
     * @param $sql
     * @return DbResultInterface
     */
    public function query($sql);

    /**
     * @param bool $autoHandleException
     * @return DbResultInterface
     */
    public function beginTransaction();

    /**
     * @return DbResultInterface
     */
    public function commit();

    /**
     * @return DbResultInterface
     */
    public function rollback();

    /**
     * @return DbResultInterface
     */
    public function onSqlReady($link, $result);
}